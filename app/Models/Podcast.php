<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Podcast extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'description', 'rss_url', 'hosts', 'artwork_url'];

    public function episodes(): HasMany
    {
        return $this->hasMany(Episode::class, 'podcast_id');
    }

    public function listeningParties(): HasMany
    {
        return $this->hasMany(ListeningParty::class);
    }


}
