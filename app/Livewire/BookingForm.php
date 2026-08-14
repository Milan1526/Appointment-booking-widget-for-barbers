<?php

namespace App\Livewire;

use App\Models\Salon;
use App\Models\Service;
use App\Models\Staff;
use App\Services\SlotFinder;
use Carbon\Carbon;
use Livewire\Component;

class BookingForm extends Component
{
    public int $currentStep = 1;

    public ?int $service_id = null;
    public ?int $staff_id = null;
    public bool $staffChosen = false;

    public ?string $date = null;
    public ?string $start_time = null;

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

    public function selectDate(string $date): void
    {
        $this->date = $date;
        $this->start_time = null; // reset slota ako se menja datum
    }

    public function selectSlot(string $time): void
    {
        $this->start_time = $time;
        $this->currentStep = 4;
    }

    public function getAvailableDaysProperty(): array
    {
        $days = [];
        $cursor = Carbon::today();

        // Sledećih 14 kalendarskih dana (uključuje i neradne, ali ih obeležavamo posebno)
        for ($i = 0; $i < 14; $i++) {
            $days[] = $cursor->copy();
            $cursor->addDay();
        }

        return $days;
    }

    public function getAvailableSlotsProperty(): array
    {
        if (! $this->date || ! $this->service_id || ! $this->staffChosen) {
            return [];
        }

        $service = Service::find($this->service_id);
        $salon = Salon::first(); // za sad jedan salon, kasnije ćemo ovo generalizovati

        return (new SlotFinder())->availableSlots(
            Carbon::parse($this->date),
            $service,
            $this->staff_id,
            $salon->id
        );
    }

    public function render()
    {
        return view('livewire.booking-form', [
            'services' => Service::where('is_active', true)->get(),
            'staff' => Staff::where('is_active', true)->get(),
        ]);
    }
}