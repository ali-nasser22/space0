<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ListeningParty extends Model
{
    use HasFactory;

    protected $fillable = ['episode_id', 'name', 'start_time'];

    public function episode(): BelongsTo
    {
        return $this->belongsTo(Episode::class);
    }
}
