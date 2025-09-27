<?php

use App\Models\ListeningParty;
use Carbon\Carbon;
use Livewire\Volt\Component;

new class extends Component {

    public ListeningParty $listeningParty;
    public bool $hasJoined = false;
    public bool $isActive = false;
    public bool $hasEnded = false;
    public int $elapsedSeconds = 0;

    public function mount(ListeningParty $listeningParty): void
    {
        $this->listeningParty = $listeningParty;
        $this->checkPartyStatus();
    }

    public function checkPartyStatus(): void
    {
        $now = Carbon::now();
        $startTime = Carbon::parse($this->listeningParty->start_time);
        $endTime = $this->listeningParty->end_time ? Carbon::parse($this->listeningParty->end_time) : null;

        $this->isActive = $startTime->isPast() && ($endTime === null || $endTime->isFuture());
        $this->hasEnded = $endTime && $endTime->isPast();

        if ($this->isActive) {
            $this->elapsedSeconds = max(0, $now->diffInSeconds($startTime));
        }
    }

    public function joinParty(): void
    {
        $this->checkPartyStatus();

        if (!$this->isActive) {
            $this->dispatch('party-error', message: 'The listening party is not currently active.');
            return;
        }

        $this->hasJoined = true;
        $this->dispatch('party-joined', [
            'mediaUrl' => $this->listeningParty->episode->media_url,
            'startOffset' => $this->elapsedSeconds,
            'partyName' => $this->listeningParty->name
        ]);
    }

    public function leaveParty(): void
    {
        $this->hasJoined = false;
        $this->dispatch('party-left');
    }

    public function getTimeUntilStart(): string
    {
        $startTime = Carbon::parse($this->listeningParty->start_time);
        $now = Carbon::now();

        if ($startTime->isPast()) {
            return 'Started';
        }

        return $startTime->diffForHumans($now);
    }

    public function getPartyStatus(): string
    {
        if ($this->hasEnded) {
            return 'ended';
        } elseif ($this->isActive) {
            return 'active';
        } else {
            return 'upcoming';
        }
    }

}; ?>

<div class="min-h-screen bg-gradient-to-br from-emerald-50 via-teal-50 to-cyan-50 py-16 px-6"
     x-data="{
        audio: null,
        isPlaying: false,
        isLoading: false,
        currentTime: 0,
        duration: 0,
        volume: 0.8,
        showError: false,
        errorMessage: '',

        initAudioPlayer(mediaUrl, startOffset = 0) {
            if (this.audio) {
                this.audio.pause();
                this.audio = null;
            }

            this.isLoading = true;
            this.audio = new Audio(mediaUrl);
            this.audio.volume = this.volume;

            this.audio.addEventListener('loadedmetadata', () => {
                this.duration = this.audio.duration;
                this.audio.currentTime = startOffset;
                this.isLoading = false;
            });

            this.audio.addEventListener('timeupdate', () => {
                this.currentTime = this.audio.currentTime;
            });

            this.audio.addEventListener('ended', () => {
                this.isPlaying = false;
            });

            this.audio.addEventListener('error', (e) => {
                this.showError = true;
                this.errorMessage = 'Failed to load audio. Please try again.';
                this.isLoading = false;
            });

            // Auto-play if the party is active
            this.audio.play().then(() => {
                this.isPlaying = true;
            }).catch(error => {
                console.log('Autoplay prevented. User must manually start playback.');
                this.isPlaying = false;
            });
        },

        togglePlayPause() {
            if (!this.audio) return;

            if (this.isPlaying) {
                this.audio.pause();
                this.isPlaying = false;
            } else {
                this.audio.play().then(() => {
                    this.isPlaying = true;
                }).catch(error => {
                    this.showError = true;
                    this.errorMessage = 'Playback failed. Please try again.';
                });
            }
        },

        setVolume(value) {
            this.volume = value;
            if (this.audio) {
                this.audio.volume = value;
            }
        },

        formatTime(seconds) {
            const minutes = Math.floor(seconds / 60);
            const remainingSeconds = Math.floor(seconds % 60);
            return `${minutes}:${remainingSeconds.toString().padStart(2, '0')}`;
        },

        stopAudio() {
            if (this.audio) {
                this.audio.pause();
                this.audio = null;
                this.isPlaying = false;
                this.currentTime = 0;
                this.duration = 0;
            }
        }
    }"
     @party-joined.window="initAudioPlayer($event.detail.mediaUrl, $event.detail.startOffset)"
     @party-left.window="stopAudio()"
     @party-error.window="showError = true; errorMessage = $event.detail.message"
     wire:poll.5s="checkPartyStatus">

    <!-- Error Toast -->
    <div x-show="showError"
         x-transition
         class="fixed top-4 right-4 bg-red-500 text-white px-6 py-4 rounded-lg shadow-lg z-50">
        <div class="flex items-center gap-3">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span x-text="errorMessage"></span>
            <button @click="showError = false" class="ml-2 hover:text-red-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>

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
                                            <a href="{{ $listeningParty->episode->media_url }}" target="_blank"
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

                                    @if($listeningParty->episode->podcast->description)
                                        <div class="bg-white/80 rounded-xl p-4 border border-gray-200/50">
                                            <p class="text-sm font-medium text-gray-700 mb-2">About this podcast</p>
                                            <p class="text-gray-600 leading-relaxed">{{ $listeningParty->episode->podcast->description }}</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif

                        <!-- Audio Player (shown when joined) -->
                        @if($hasJoined)
                            <div x-show="audio"
                                 class="bg-gradient-to-br from-emerald-50 to-teal-50 rounded-2xl p-6 border border-emerald-200/50">
                                <div class="flex items-center gap-3 mb-6">
                                    <div
                                        class="w-8 h-8 bg-gradient-to-r from-emerald-500 to-teal-500 rounded-lg flex items-center justify-center">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor"
                                             viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728"/>
                                        </svg>
                                    </div>
                                    <h2 class="text-xl font-bold text-gray-900">Now Playing</h2>
                                </div>

                                <div class="space-y-4">
                                    <!-- Playback Controls -->
                                    <div class="flex items-center gap-4">
                                        <button @click="togglePlayPause()"
                                                :disabled="isLoading"
                                                class="w-12 h-12 bg-emerald-600 hover:bg-emerald-700 disabled:bg-gray-400 text-white rounded-full flex items-center justify-center transition-colors">
                                            <template x-if="isLoading">
                                                <svg class="w-5 h-5 animate-spin" fill="none" stroke="currentColor"
                                                     viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          stroke-width="2"
                                                          d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                                </svg>
                                            </template>
                                            <template x-if="!isLoading && !isPlaying">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                     viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          stroke-width="2"
                                                          d="M14.828 14.828a4 4 0 01-5.656 0M9 10h1.586a1 1 0 01.707.293l2.414 2.414a1 1 0 00.707.293H15"/>
                                                </svg>
                                            </template>
                                            <template x-if="!isLoading && isPlaying">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                     viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          stroke-width="2" d="M10 9v6m4-6v6"/>
                                                </svg>
                                            </template>
                                        </button>

                                        <div class="flex-1">
                                            <div class="flex justify-between text-sm text-gray-600 mb-1">
                                                <span x-text="formatTime(currentTime)">0:00</span>
                                                <span x-text="formatTime(duration)">0:00</span>
                                            </div>
                                            <div class="w-full bg-gray-200 rounded-full h-2">
                                                <div class="bg-emerald-600 h-2 rounded-full transition-all duration-100"
                                                     :style="`width: ${duration > 0 ? (currentTime / duration) * 100 : 0}%`"></div>
                                            </div>
                                        </div>

                                        <div class="flex items-center gap-2">
                                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor"
                                                 viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M15.536 8.464a5 5 0 010 7.072"/>
                                            </svg>
                                            <input type="range" min="0" max="1" step="0.1"
                                                   :value="volume"
                                                   @input="setVolume($event.target.value)"
                                                   class="w-20 h-2 bg-gray-200 rounded-lg">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                    </div>

                    <div class="space-y-6">
                        <div
                            class="bg-white rounded-2xl p-6 shadow-lg shadow-gray-100/50 border border-gray-100/50 sticky top-8">
                            <div class="text-center space-y-6">
                                @if($listeningParty->episode->podcast->artwork_url ?? false)
                                    <div class="relative">
                                        <img src="{{ $listeningParty->episode->podcast->artwork_url }}"
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
                                    <!-- Party Status Badge -->
                                    <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-semibold
                                        @if($this->getPartyStatus() === 'active') bg-green-50 text-green-700
                                        @elseif($this->getPartyStatus() === 'ended') bg-red-50 text-red-700
                                        @else bg-emerald-50 text-emerald-700 @endif">

                                        @if($this->getPartyStatus() === 'active')
                                            <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                                            Live Now
                                        @elseif($this->getPartyStatus() === 'ended')
                                            <div class="w-2 h-2 bg-red-500 rounded-full"></div>
                                            Ended
                                        @else
                                            <div class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></div>
                                            Upcoming
                                        @endif
                                    </div>

                                    <!-- Time Display -->
                                    <div class="space-y-2">
                                        @if($this->getPartyStatus() === 'upcoming')
                                            <p class="text-sm text-gray-500 font-medium">Party starts in</p>
                                            <div class="bg-gray-50 rounded-xl p-4">
                                                <div class="text-2xl font-bold text-gray-900 font-mono">
                                                    {{ $getTimeUntilStart() }}
                                                </div>
                                            </div>
                                        @elseif($this->getPartyStatus() === 'active')
                                            <p class="text-sm text-gray-500 font-medium">Party is live!</p>
                                            <div class="bg-green-50 rounded-xl p-4">
                                                <div class="text-lg font-bold text-green-700">
                                                    {{ floor($elapsedSeconds / 60) }}
                                                    :{{ str_pad($elapsedSeconds % 60, 2, '0', STR_PAD_LEFT) }} elapsed
                                                </div>
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Join/Leave Button -->
                                    @if(!$hasJoined)
                                        <button wire:click="joinParty"
                                                @if($this->getPartyStatus() !== 'active') disabled @endif
                                                class="w-full bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 disabled:from-gray-400 disabled:to-gray-500 text-white font-bold py-4 px-6 rounded-xl shadow-lg shadow-emerald-200 hover:shadow-xl hover:shadow-emerald-300 disabled:shadow-gray-200 transform hover:-translate-y-0.5 disabled:transform-none transition-all duration-200 disabled:cursor-not-allowed">
                                            @if($this->getPartyStatus() === 'active')
                                                Join Listening Party
                                            @elseif($this->getPartyStatus() === 'ended')
                                                Party Has Ended
                                            @else
                                                Party Not Started
                                            @endif
                                        </button>
                                    @else
                                        <button wire:click="leaveParty"
                                                class="w-full bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 text-white font-bold py-4 px-6 rounded-xl shadow-lg shadow-red-200 hover:shadow-xl hover:shadow-red-300 transform hover:-translate-y-0.5 transition-all duration-200">
                                            Leave Party
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
