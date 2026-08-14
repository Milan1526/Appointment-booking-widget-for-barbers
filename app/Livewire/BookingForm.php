<?php

namespace App\Livewire;

use App\Models\Service;
use Livewire\Component;

class BookingForm extends Component
{
    public int $currentStep = 1;

    public ?int $service_id = null;

    public function selectService(int $serviceId): void
    {
        $this->service_id = $serviceId;
        $this->currentStep = 2;
    }

    public function goToStep(int $step): void
    {
        $this->currentStep = $step;
    }

    public function render()
    {
        return view('livewire.booking-form', [
            'services' => Service::where('is_active', true)->get(),
        ]);
    }
}