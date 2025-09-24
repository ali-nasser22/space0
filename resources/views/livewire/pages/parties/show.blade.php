<?php

use App\Models\ListeningParty;
use Livewire\Volt\Component;

new class extends Component {

    public ListeningParty $listening_party;

    public function mount(ListeningParty $listeningParty): void
    {
        $this->listening_party = $listeningParty;
    }


}; ?>

<div>
    {{$listening_party->episode}}
</div>
