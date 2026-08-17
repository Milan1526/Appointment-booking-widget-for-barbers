<?php

namespace App\Livewire;
use App\Mail\AppointmentConfirmation;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;

use App\Models\Appointment;
use App\Models\Salon;
use App\Models\Service;
use App\Models\Staff;
use App\Services\SlotFinder;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Livewire\Component;

class BookingForm extends Component
{
    public int $currentStep = 1;

    public ?int $service_id = null;
    public ?int $staff_id = null;
    public bool $staffChosen = false;

    public ?string $date = null;
    public ?string $start_time = null;

    public string $customer_name = '';
    public string $customer_email = '';
    public string $customer_phone = '';

    public bool $bookingComplete = false;

    protected function rules(): array
    {
        return [
            'customer_name' => 'required|string|min:2|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => 'required|string|min:6|max:30',
        ];
    }

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
        $this->start_time = null;
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
        $salon = Salon::first();

        return (new SlotFinder())->availableSlots(
            Carbon::parse($this->date),
            $service,
            $this->staff_id,
            $salon->id
        );
    }

    public function submit(): void
    {
        $this->validate();

        $rateLimitKey = 'booking-attempts:' . request()->ip();

        if (RateLimiter::tooManyAttempts($rateLimitKey, maxAttempts: 4)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);
            $hours = ceil($seconds / 3600);

            $this->addError('general', "Dostigao si limit zakazivanja sa ove IP adrese. Pokušaj ponovo za otprilike {$hours}h.");
            return;
        }

        $hasPendingAppointment = Appointment::where('customer_email', $this->customer_email)
        ->where('status', 'pending')
        ->where('confirmation_expires_at', '>', now())
        ->exists();

        if ($hasPendingAppointment) {
            $this->addError('general', 'Već imaš termin koji čeka potvrdu na ovaj email. Proveri svoj inbox, ili sačekaj da prethodni istekne.');
            return;
        }

        if (! $this->service_id || ! $this->date || ! $this->start_time) {
            $this->addError('general', 'Nedostaju podaci o terminu. Vrati se na prethodne korake.');
            return;
        }

        $service = Service::findOrFail($this->service_id);
        $salon = Salon::first();

        // Ponovo proveri dostupnost — sprečava race-condition ako je neko drugi baš zauzeo taj slot dok si ti popunjavao podatke
        $stillAvailable = (new SlotFinder())->availableSlots(
            Carbon::parse($this->date),
            $service,
            $this->staff_id,
            $salon->id
        );

        if (! in_array($this->start_time, $stillAvailable)) {
            $this->addError('general', 'Nažalost, taj termin je upravo zauzet. Izaberi drugi.');
            $this->start_time = null;
            $this->currentStep = 3;
            return;
        }

        $startTime = Carbon::parse($this->start_time);
        $endTime = $startTime->copy()->addMinutes($service->duration_minutes);

        $appointment = Appointment::create([
        'salon_id' => $salon->id,
        'staff_id' => $this->staff_id,
        'service_id' => $this->service_id,
        'customer_name' => $this->customer_name,
        'customer_email' => $this->customer_email,
        'customer_phone' => $this->customer_phone,
        'date' => $this->date,
        'start_time' => $startTime->format('H:i'),
        'end_time' => $endTime->format('H:i'),
        'status' => 'pending',
        'confirmation_token' => Str::random(40),
        'confirmation_expires_at' => now()->addMinutes(15),
    ]);

    Mail::to($appointment->customer_email)->send(new AppointmentConfirmation($appointment));

    RateLimiter::hit($rateLimitKey, decaySeconds: 60 * 60 * 24); // broji ovaj pokušaj, pamti 24h

    $this->bookingComplete = true;
    }

    public function bookAnother(): void
    {
        $this->reset([
            'currentStep', 'service_id', 'staff_id', 'staffChosen',
            'date', 'start_time', 'customer_name', 'customer_email',
            'customer_phone', 'bookingComplete',
        ]);
        $this->currentStep = 1;
    }

    public function render()
    {
        return view('livewire.booking-form', [
            'services' => Service::where('is_active', true)->get(),
            'staff' => Staff::where('is_active', true)->get(),
        ]);
    }
}