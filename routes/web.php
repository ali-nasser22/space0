<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('home');
})->name('home');

Volt::route('/parties/{listeningParty}', 'pages.parties.show')->name('listening-parties.show');


require __DIR__.'/auth.php';
