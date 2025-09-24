<?php

namespace App\Jobs;

use App\Models\Episode;
use App\Models\ListeningParty;
use App\Models\Podcast;
use Carbon\CarbonInterval;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;

class ProcessPodcastUrlJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $rssUrl;
    public ListeningParty $listeningParty;
    public Episode $episode;

    public function __construct(string $rssUrl, ListeningParty $listeningParty, Episode $episode)
    {
        $this->rssUrl = $rssUrl;
        $this->listeningParty = $listeningParty;
        $this->episode = $episode;
    }

    public function handle(): void
    {
        try {
            // Load RSS feed
            $xml = @simplexml_load_file($this->rssUrl);
            if (!$xml) {
                throw new Exception("Failed to load RSS feed from {$this->rssUrl}");
            }

            // Extract podcast metadata
            $podcastTitle = (string) $xml->channel->title ?? 'Untitled Podcast';
            $podcastArtWorkUrl = (string) $xml->channel->image->url ?? null;
            $latestEpisode = $xml->channel->item[0] ?? null;

            if (!$latestEpisode) {
                throw new Exception("No episodes found in RSS feed.");
            }

            $episodeTitle = (string) $latestEpisode->title ?? 'Untitled Episode';
            $episodeMediaUrl = (string) $latestEpisode->enclosure['url'] ?? null;

            // Handle iTunes namespace and duration
            $nameSpaces = $xml->getNamespaces(true);
            $itunesNameSpace = $nameSpaces['itunes'] ?? null;


            $episodeLength = (string) $latestEpisode->children($itunesNameSpace)->duration;

            if (empty($episodeLength)) {
                $fileSize = (int) $latestEpisode->enclosure['length'];
                $bitrate = 128000;
                $durationInSeconds = ceil($fileSize * 8 / $bitrate);
                $episodeLength = (string) $durationInSeconds;
            }


            // Normalize duration
            try {
                $interval = CarbonInterval::createFromFormat("H:i:s", $episodeLength);
            } catch (Exception $e) {
                $interval = CarbonInterval::seconds((int) $episodeLength);
            }

            // Calculate end time
            $endTime = $this->listeningParty->start_time->copy()->add($interval);

            // Create or update podcast
            $podcast = Podcast::updateOrCreate(
                ['rss_url' => $this->rssUrl],
                ['title' => $podcastTitle, 'artwork_url' => $podcastArtWorkUrl]
            );

            // Associate episode and update
            if (!$this->episode) {
                throw new Exception("Episode instance is missing.");
            }

            $this->episode->podcast()->associate($podcast);
            $this->episode->update([
                'title' => $episodeTitle,
                'media_url' => $episodeMediaUrl,
            ]);

            // Update listening party
            if (!$this->listeningParty) {
                throw new Exception("Listening party instance is missing.");
            }

            $this->listeningParty->update([
                'end_time' => $endTime,
            ]);

        } catch (Exception $e) {
            Log::error("Podcast sync failed: ".$e->getMessage());
            // Optionally rethrow or handle gracefully
            throw $e;
        }
    }
}
