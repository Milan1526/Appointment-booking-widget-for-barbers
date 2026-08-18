<?php

use App\Livewire\BookingForm;
use App\Models\Salon;
use App\Models\Service;
use App\Models\Staff;
use Carbon\Carbon;
use Livewire\Livewire;

it('rejects a booking attempt for a date in the past even if forced via component', function () {
    $salon = Salon::create(['name' => 'Test Salon', 'slug' => 'test-salon']);
    $staff = Staff::create(['salon_id' => $salon->id, 'name' => 'Boki']);
    $service = Service::create(['salon_id' => $salon->id, 'name' => 'Šišanje', 'price' => 800, 'duration_minutes' => 30]);

    $pastDate = Carbon::yesterday()->toDateString();

    Livewire::test(BookingForm::class)
        ->set('service_id', $service->id)
        ->set('staff_id', $staff->id)
        ->set('staffChosen', true)
        ->set('date', $pastDate)
        ->set('start_time', '10:00')
        ->set('customer_name', 'Test Kupac')
        ->set('customer_email', 'test@example.com')
        ->set('customer_phone', '064123456')
        ->call('submit')
        ->assertHasErrors('general');

    expect(\App\Models\Appointment::count())->toBe(0);
});

it('rejects a booking attempt for a non-working day even if forced via component', function () {
    $salon = Salon::create(['name' => 'Test Salon', 'slug' => 'test-salon']);
    $staff = Staff::create(['salon_id' => $salon->id, 'name' => 'Boki']);
    $service = Service::create(['salon_id' => $salon->id, 'name' => 'Šišanje', 'price' => 800, 'duration_minutes' => 30]);

    // Pronađi sledeću nedelju (neradni dan po config-u)
    $sunday = Carbon::parse('next sunday')->toDateString();

    Livewire::test(BookingForm::class)
        ->set('service_id', $service->id)
        ->set('staff_id', $staff->id)
        ->set('staffChosen', true)
        ->set('date', $sunday)
        ->set('start_time', '10:00')
        ->set('customer_name', 'Test Kupac')
        ->set('customer_email', 'test2@example.com')
        ->set('customer_phone', '064123456')
        ->call('submit')
        ->assertHasErrors('general');

    expect(\App\Models\Appointment::count())->toBe(0);
});