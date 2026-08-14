<?php

namespace App\Livewire;

use App\Models\Service;
use App\Models\Staff;
use Livewire\Component;

class BookingForm extends Component
{
    public int $currentStep = 1;

    public ?int $service_id = null;
    public ?int $staff_id = null;
    public bool $staffChosen = false; // razlikuje "nije još biran" od "biran je Bilo ko (null)"

    public function selectService(int $serviceId): void
    {
        $this->service_id = $serviceId;
        $this->currentStep = 2;
    }

    public function selectStaff(?int $staffId): void
    {
        $this->staff_id = $staffId;
        $this->staffChosen = true;
        $this->currentStep = 3;
    }

    public function render()
    {
        return view('livewire.booking-form', [
            'services' => Service::where('is_active', true)->get(),
            'staff' => Staff::where('is_active', true)->get(),
        ]);
    }
}