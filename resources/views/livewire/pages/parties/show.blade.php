<?php

use App\Models\ListeningParty;
use Carbon\Carbon;
use Livewire\Volt\Component;

new class extends Component {

    public ListeningParty $listeningParty;

    public function mount(ListeningParty $listeningParty): void
    {
        $this->listeningParty = $listeningParty;
    }


}; ?>

<div class="min-h-screen bg-gradient-to-br from-emerald-50 via-teal-50 to-cyan-50 py-16 px-6">
    <div class="max-w-4xl mx-auto space-y-8">

        <nav class="flex items-center gap-2 text-sm">
            <a href="{{ route('home') }}"
               class="flex items-center gap-2 px-3 py-2 text-gray-500 hover:text-emerald-600 rounded-lg hover:bg-white/50 transition-all duration-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Back to Parties
            </a>
            <span class="text-gray-300">/</span>
            <span class="text-gray-700 font-medium">{{ $listeningParty->name }}</span>
        </nav>

        <div
            class="bg-white/80 backdrop-blur-lg rounded-3xl shadow-2xl shadow-emerald-100/50 overflow-hidden border border-white/20">

            <div class="relative h-32 bg-gradient-to-r from-emerald-600 via-teal-600 to-cyan-600 overflow-hidden">
                <div class="absolute inset-0 bg-black/10"></div>
                <div class="absolute bottom-6 left-8 right-8 flex items-end justify-between">
                    <div class="flex items-center gap-4">
                        <div
                            class="w-16 h-16 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center text-3xl border border-white/30">
                            🎧
                        </div>
                        <div>
                            <h1 class="text-3xl font-bold text-white mb-1">{{ $listeningParty->name }}</h1>
                            <div class="flex items-center gap-2 text-emerald-100">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span class="text-sm font-medium">
                                    {{ Carbon::parse($listeningParty->start_time)->format('M d, Y h:i A') }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="p-8 space-y-8">

                <div class="grid lg:grid-cols-3 gap-8">
                    <div class="lg:col-span-2 space-y-8">

                        <div
                            class="bg-gradient-to-br from-gray-50 to-gray-100/50 rounded-2xl p-6 border border-gray-200/50">
                            <div class="flex items-center gap-3 mb-6">
                                <div
                                    class="w-8 h-8 bg-gradient-to-r from-blue-500 to-indigo-500 rounded-lg flex items-center justify-center">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor"
                                         viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/>
                                    </svg>
                                </div>
                                <h2 class="text-xl font-bold text-gray-900">Episode Details</h2>
                            </div>

                            <div class="space-y-4">
                                @if($listeningParty->episode->title)
                                    <div>
                                        <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ $listeningParty->episode->title }}</h3>
                                    </div>
                                @endif

                                <div class="bg-white/80 rounded-xl p-4 border border-gray-200/50">
                                    <div class="flex items-start gap-3">
                                        <div
                                            class="w-6 h-6 bg-emerald-100 rounded-lg flex items-center justify-center mt-0.5">
                                            <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor"
                                                 viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.102m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                                            </svg>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-gray-700 mb-1">Episode Media URL</p>
                                            <a href="{{ $listeningParty->episode->media_url }}"
                                               target="_blank"
                                               class="text-sm text-emerald-600 hover:text-emerald-700 font-medium break-all hover:underline transition-colors">
                                                {{ $listeningParty->episode->media_url }}
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if($listeningParty->episode->podcast)
                            <div
                                class="bg-gradient-to-br from-gray-50 to-gray-100/50 rounded-2xl p-6 border border-gray-200/50">
                                <div class="flex items-center gap-3 mb-6">
                                    <div
                                        class="w-8 h-8 bg-gradient-to-r from-purple-500 to-pink-500 rounded-lg flex items-center justify-center">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor"
                                             viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/>
                                        </svg>
                                    </div>
                                    <h2 class="text-xl font-bold text-gray-900">Podcast Information</h2>
                                </div>

                                <div class="space-y-4">
                                    <div>
                                        <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ $listeningParty->episode->podcast->title }}</h3>
                                    </div>

                                    @if($listeningParty->podcast->description)
                                        <div class="bg-white/80 rounded-xl p-4 border border-gray-200/50">
                                            <p class="text-sm font-medium text-gray-700 mb-2">About this podcast</p>
                                            <p class="text-gray-600 leading-relaxed">{{ $listeningParty->episode->podcast->description }}</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif

                    </div>

                    <div class="space-y-6">
                        <div
                            class="bg-white rounded-2xl p-6 shadow-lg shadow-gray-100/50 border border-gray-100/50 sticky top-8">
                            <div class="text-center space-y-6">
                                @if($listeningParty->podcast->artwork_url)
                                    <div class="relative">
                                        <img src="{{$listeningParty->podcast->artwork_url}}"
                                             alt="Podcast Artwork"
                                             class="w-full aspect-square object-cover rounded-2xl shadow-lg">
                                        <div
                                            class="absolute inset-0 rounded-2xl bg-gradient-to-t from-black/20 to-transparent"></div>
                                    </div>
                                @else
                                    <div
                                        class="w-full aspect-square bg-gradient-to-br from-emerald-100 to-teal-100 rounded-2xl flex items-center justify-center">
                                        <span class="text-6xl opacity-60">🎵</span>
                                    </div>
                                @endif

                                <div class="space-y-3">
                                    <div
                                        class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-50 text-emerald-700 rounded-full text-sm font-semibold">
                                        <div class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></div>
                                        Listening Party
                                    </div>

                                    <div class="space-y-2">
                                        <p class="text-sm text-gray-500 font-medium">Party starts in</p>
                                        <div class="bg-gray-50 rounded-xl p-4">
                                            <div class="text-2xl font-bold text-gray-900 font-mono">
                                                {{ Carbon::parse($listeningParty->start_time)->diffForHumans() }}
                                            </div>
                                        </div>
                                    </div>

                                    <button
                                        class="w-full bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-bold py-4 px-6 rounded-xl shadow-lg shadow-emerald-200 hover:shadow-xl hover:shadow-emerald-300 transform hover:-translate-y-0.5 transition-all duration-200">
                                        Join Listening Party
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
