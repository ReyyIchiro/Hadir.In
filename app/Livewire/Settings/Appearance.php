<?php

namespace App\Livewire\Settings;

use App\Livewire\Concerns\WithTenant;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Appearance extends Component
{
    use WithTenant;

    public function render(): View
    {
        return view('livewire.settings.appearance')
            ->layoutData(['heading' => 'Tampilan'])
            ->title('Tampilan');
    }
}
