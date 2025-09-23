<?php

namespace Database\Factories;

use App\Models\podcast;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class podcastFactory extends Factory
{
    protected $model = podcast::class;

    public function definition(): array
    {
        return [
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
