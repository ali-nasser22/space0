<?php

use App\Models\ListeningParty;
use Livewire\Volt\Component;

new class extends Component {

    public string $name = '';
    public string $start_time;

    public function createListeningParty()
    {

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
            </flux:input.group>

            <flux:input.group @class('flex flex-col gap-1')>
                <flux:label class="text-sm font-medium text-gray-700">Start Time</flux:label>
                <flux:description class="text-xs text-gray-500">Enter the start time of the listening party
                </flux:description>
                <flux:input wire:model="start_time" type="date"
                />
            </flux:input.group>

            <flux:button type="submit" @class('w-full') variant="primary" color="emerald">
                Create Listening Party
            </flux:button>

        </form>
    </div>
</div>
