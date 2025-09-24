<?php

use App\Models\Episode;
use App\Models\ListeningParty;
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

        // first check that there are no existing episodes with the same name

        $episode = Episode::create([
            'media_url' => $this->media_url,
        ]);

        $listening_party = ListeningParty::create([
            'episode_id' => $episode->id,
            'name' => $this->name,
            'start_time' => $this->start_time
        ]);

        return redirect()->route('listening-parties.show', $listening_party);
        // if there is, use that, if not create a new one
        // when a new episode is created, grab info with bg job
        //then use info to create a new listening party


    }


    public function with(): array
    {
        return [
            'listening_parties' => ListeningParty::all(),
        ];
    }

}


?>
<div class="flex items-center justify-center min-h-screen bg-slate-100 py-12 px-4 flex-col gap-6">
    <h2 class="text-2xl font-mono">Listening Party</h2>
    <div class="w-full max-w-xl bg-white rounded-xl shadow-lg p-8">
        <form wire:submit="createListeningParty" class="space-y-8">

            <flux:input.group @class('flex flex-col gap-1')>
                <flux:label class="text-sm font-medium text-gray-700">Name</flux:label>
                <flux:description class="text-xs text-gray-500">Enter the name of the listening party</flux:description>
                <flux:input wire:model="name"
                />
                @error('name')
                <flux:text size="text-xs" color="red"> {{ $message }}</flux:text>
                @enderror
            </flux:input.group>

            <flux:input.group @class('flex flex-col gap-1')>
                <flux:label class="text-sm font-medium text-gray-700">Start Time</flux:label>
                <flux:description class="text-xs text-gray-500">Enter the start time of the listening party
                </flux:description>
                <flux:input wire:model="start_time" type="date"
                />
                @error('start_time')
                <flux:text size="text-xs" color="red"> {{ $message }}</flux:text>
                @enderror
            </flux:input.group>

            <flux:input.group @class('flex flex-col gap-1')>
                <flux:label class="text-sm font-medium text-gray-700">Episode</flux:label>
                <flux:description class="text-xs text-gray-500">Enter the url of the episode</flux:description>
                <flux:input wire:model="media_url"
                />
                @error('media_url')
                <flux:text size="text-xs" color="red"> {{ $message }}</flux:text>
                @enderror
            </flux:input.group>

            <flux:button type="submit" @class('w-full') variant="primary" color="emerald">
                Create Listening Party
            </flux:button>

        </form>
    </div>
</div>
