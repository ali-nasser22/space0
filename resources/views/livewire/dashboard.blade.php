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

<div>

</div>
