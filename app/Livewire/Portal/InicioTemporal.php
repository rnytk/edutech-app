<?php

namespace App\Livewire\Portal;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.portal-estudiante')]
class InicioTemporal extends Component
{
    public function render(): View
    {
        return view('livewire.portal.inicio-temporal');
    }
}
