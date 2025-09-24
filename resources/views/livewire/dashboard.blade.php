<?php

use App\Jobs\ProcessPodcastUrlJob;
use App\Models\Episode;
use App\Models\ListeningParty;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use JetBrains\PhpStorm\NoReturn;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;

new class extends Component {

    #[Validate('required|string')]
    public string $name = '';
    #[Validate('required|date')]
    public string $start_time;
    #[Validate('required|url')]
    public string $media_url;

    public function createListeningParty()
    {
        $this->validate();

        $episode = Episode::create([
            'media_url' => $this->media_url,
        ]);

        $listening_party = ListeningParty::create([
            'episode_id' => $episode->id,
            'name' => $this->name,
            'start_time' => $this->start_time
        ]);

        ProcessPodcastUrlJob::dispatchSync($this->media_url, $listening_party, $episode);

        return redirect()->route('listening-parties.show', $listening_party);

    }


    public function with(): array
    {
        return [
            'listening_parties' => ListeningParty::where('is_active',
                true)->orderBy('start_time')->with('episode.podcast')->get(),
        ];
    }

}


?>
<div class="min-h-screen bg-gradient-to-br from-emerald-50 via-teal-50 to-cyan-50">
    <div class="container mx-auto px-6 py-16">
        <header class="text-center mb-16">
            <h1 class="font-mono text-6xl font-bold bg-gradient-to-r from-emerald-600 via-teal-600 to-cyan-600 bg-clip-text text-transparent mb-4">
                Space-0 〽️
            </h1>
            <p class="text-xl text-gray-600 font-light">Synchronized listening experiences</p>
        </header>

        <div class="grid lg:grid-cols-3 gap-8 max-w-7xl mx-auto">
            <div class="lg:col-span-2">
                <div
                    class="bg-white/80 backdrop-blur-lg rounded-3xl shadow-2xl shadow-emerald-100/50 p-8 border border-white/20">
                    <div class="flex items-center gap-4 mb-8">
                        <div
                            class="w-12 h-12 bg-gradient-to-r from-emerald-500 to-teal-500 rounded-2xl flex items-center justify-center">
                            <span class="text-2xl">🎧</span>
                        </div>
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900">Create Listening Party</h2>
                            <p class="text-gray-500">Set up a synchronized listening experience</p>
                        </div>
                    </div>

                    <form wire:submit="createListeningParty" class="space-y-8">
                        <div class="space-y-2">
                            <flux:label class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Party Name
                            </flux:label>
                            <flux:input wire:model="name"
                                        class="w-full px-4 py-4 bg-gray-50/50 border-2 border-gray-100 rounded-xl focus:border-emerald-400 focus:ring-4 focus:ring-emerald-100 transition-all duration-200 text-lg"
                                        placeholder="My Awesome Listening Party"/>
                            @error('name')
                            <flux:text class="text-sm text-red-500 mt-1 font-medium">{{ $message }}</flux:text>
                            @enderror
                        </div>

                        <div class="space-y-2">
                            <flux:label class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Podcast RSS
                                Feed
                            </flux:label>
                            <flux:input wire:model="media_url"
                                        class="w-full px-4 py-4 bg-gray-50/50 border-2 border-gray-100 rounded-xl focus:border-emerald-400 focus:ring-4 focus:ring-emerald-100 transition-all duration-200 text-lg"
                                        placeholder="https://example.com/podcast.rss"/>
                            <p class="text-xs text-gray-500 mt-1">We'll automatically grab the latest episode</p>
                            @error('media_url')
                            <flux:text class="text-sm text-red-500 mt-1 font-medium">{{ $message }}</flux:text>
                            @enderror
                        </div>

                        <div class="space-y-2">
                            <flux:label class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Start Time
                            </flux:label>
                            <flux:input wire:model="start_time"
                                        type="datetime-local"
                                        min="{{ now()->format('Y-m-d\TH:i') }}"
                                        class="w-full px-4 py-4 bg-gray-50/50 border-2 border-gray-100 rounded-xl focus:border-emerald-400 focus:ring-4 focus:ring-emerald-100 transition-all duration-200 text-lg"/>
                            @error('start_time')
                            <flux:text class="text-sm text-red-500 mt-1 font-medium">{{ $message }}</flux:text>
                            @enderror
                        </div>

                        <flux:button type="submit"
                                     variant="primary"
                                     class="w-full py-4 px-8 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-bold rounded-xl shadow-lg shadow-emerald-200 hover:shadow-xl hover:shadow-emerald-300 transform hover:-translate-y-0.5 transition-all duration-200 text-lg">
                            Create Listening Party
                        </flux:button>
                    </form>
                </div>
            </div>

            <div class="space-y-6">
                <div class="flex items-center gap-3 mb-6">
                    <div
                        class="w-8 h-8 bg-gradient-to-r from-rose-500 to-pink-500 rounded-lg flex items-center justify-center">
                        <span class="text-lg">🎉</span>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900">Upcoming Parties</h2>
                </div>

                <div class="space-y-4">
                    @if($listening_parties->isNotEmpty())
                        @foreach($listening_parties as $party)
                            <div
                                class="bg-white/80 backdrop-blur-lg rounded-2xl shadow-lg shadow-gray-100/50 border border-white/20 overflow-hidden hover:shadow-xl hover:shadow-gray-200/50 transition-all duration-300 group"
                                wire:key="{{$party->id}}">

                                @if($party->episode && $party->podcast)
                                    <div
                                        class="h-24 bg-gradient-to-r from-emerald-500/10 to-teal-500/10 relative overflow-hidden">
                                        <img src="{{$party->podcast->artwork_url}}"
                                             alt="{{$party->podcast->title}}"
                                             class="absolute right-4 top-4 w-16 h-16 rounded-xl object-cover shadow-md border-2 border-white/50">
                                        <div
                                            class="absolute inset-0 bg-gradient-to-r from-black/20 to-transparent"></div>
                                    </div>
                                @endif

                                <div class="p-6">
                                    <div class="flex items-start justify-between mb-4">
                                        <div class="flex-1">
                                            <h3 class="text-xl font-bold text-gray-900 mb-1 group-hover:text-emerald-600 transition-colors">
                                                {{ $party->name }}
                                            </h3>
                                            @if($party->episode && $party->podcast)
                                                <p class="text-sm text-gray-500 font-medium">
                                                    {{ $party->podcast->title }}
                                                </p>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="mb-6" x-data="{
                                        startTime: '{{$party->start_time->toIso8601String()}}',
                                        countdownText: '',
                                        isLive: {{$party->start_time->isPast() && $party->is_active ? 'true' : 'false'}},
                                        updateCountDown() {
                                            const start = new Date(this.startTime).getTime();
                                            const now = new Date().getTime();
                                            const distance = start - now;
                                            if(distance < 0) {
                                                this.countdownText = 'Started';
                                                this.isLive = true;
                                            } else {
                                                this.isLive = false;
                                                const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                                                const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                                                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                                                const seconds = Math.floor((distance % (1000 * 60)) / 1000);
                                                this.countdownText = `${days}d ${hours}h ${minutes}m ${seconds}s`;
                                            }
                                        }
                                    }" x-init="updateCountDown(); setInterval(() => updateCountDown(), 1000)">

                                        <div x-show="isLive" class="flex items-center gap-2">
                                            <div
                                                class="flex items-center gap-2 px-3 py-1.5 bg-red-100 text-red-700 rounded-full text-sm font-bold">
                                                <div class="w-2 h-2 bg-red-500 rounded-full animate-pulse"></div>
                                                LIVE NOW
                                            </div>
                                        </div>

                                        <div x-show="!isLive" class="flex items-center gap-2">
                                            <div
                                                class="px-3 py-1.5 bg-gray-100 text-gray-600 rounded-full text-sm font-medium">
                                                Starts in <span x-text="countdownText"
                                                                class="font-mono font-bold"></span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex gap-3">
                                        <flux:button href="{{ route('listening-parties.show', $party) }}"
                                                     class="flex-1 px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-lg transition-colors duration-200 text-center">
                                            View Details
                                        </flux:button>

                                        <div x-show="isLive">
                                            <flux:button
                                                class="px-6 py-2.5 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white font-bold rounded-lg shadow-md hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-200">
                                                Join Now
                                            </flux:button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="bg-white/60 backdrop-blur-sm rounded-2xl p-8 text-center border border-white/20">
                            <div
                                class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <span class="text-2xl opacity-50">🎵</span>
                            </div>
                            <p class="text-gray-500 font-medium">No parties scheduled yet</p>
                            <p class="text-sm text-gray-400 mt-1">Be the first to create an amazing listening
                                experience!</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
