<?php

use App\Models\Appointment;
use App\Models\Salon;
use App\Models\Service;
use App\Models\Staff;
use App\Services\SlotFinder;
use Carbon\Carbon;

it('returns empty slots on a non-working day (sunday)', function () {
    $salon = Salon::create(['name' => 'Test Salon', 'slug' => 'test-salon']);
    $service = Service::create(['salon_id' => $salon->id, 'name' => 'Šišanje', 'price' => 800, 'duration_minutes' => 30]);

    $sunday = Carbon::parse('next sunday');

    $slots = (new SlotFinder())->availableSlots($sunday, $service, null, $salon->id);

    expect($slots)->toBeEmpty();
});

it('excludes a slot that overlaps with an existing confirmed appointment for the chosen staff', function () {
    $salon = Salon::create(['name' => 'Test Salon', 'slug' => 'test-salon']);
    $staff = Staff::create(['salon_id' => $salon->id, 'name' => 'Boki']);
    $service = Service::create(['salon_id' => $salon->id, 'name' => 'Šišanje', 'price' => 800, 'duration_minutes' => 30]);

    $monday = Carbon::parse('next monday');

    Appointment::create([
        'salon_id' => $salon->id,
        'staff_id' => $staff->id,
        'service_id' => $service->id,
        'customer_name' => 'Test Kupac',
        'customer_email' => 'test@example.com',
        'customer_phone' => '000',
        'date' => $monday->toDateString(),
        'start_time' => '10:00',
        'end_time' => '10:30',
        'status' => 'confirmed',
    ]);

    $slots = (new SlotFinder())->availableSlots($monday, $service, $staff->id, $salon->id);

    expect($slots)->not->toContain('10:00');
    expect($slots)->toContain('09:00');
    expect($slots)->toContain('10:30');
});

it('offers a slot for "anyone" if at least one staff member is free', function () {
    $salon = Salon::create(['name' => 'Test Salon', 'slug' => 'test-salon']);
    $boki = Staff::create(['salon_id' => $salon->id, 'name' => 'Boki']);
    $sale = Staff::create(['salon_id' => $salon->id, 'name' => 'Sale']);
    $service = Service::create(['salon_id' => $salon->id, 'name' => 'Šišanje', 'price' => 800, 'duration_minutes' => 30]);

    $monday = Carbon::parse('next monday');

    // Boki je zauzet u 10:00, Sale je slobodan
    Appointment::create([
        'salon_id' => $salon->id,
        'staff_id' => $boki->id,
        'service_id' => $service->id,
        'customer_name' => 'Test Kupac',
        'customer_email' => 'test@example.com',
        'customer_phone' => '000',
        'date' => $monday->toDateString(),
        'start_time' => '10:00',
        'end_time' => '10:30',
        'status' => 'confirmed',
    ]);

    $slots = (new SlotFinder())->availableSlots($monday, $service, null, $salon->id);

    expect($slots)->toContain('10:00'); // jer je Sale slobodan
});

it('does not offer slots earlier than the minimum notice period today', function () {
    Carbon::setTestNow(Carbon::parse('today 10:00')); // simuliramo da je "sada" 10:00

    $salon = Salon::create(['name' => 'Test Salon', 'slug' => 'test-salon']);
    Staff::create(['salon_id' => $salon->id, 'name' => 'Boki']);
    $service = Service::create(['salon_id' => $salon->id, 'name' => 'Šišanje', 'price' => 800, 'duration_minutes' => 30]);

    $today = Carbon::today();

    // Preskačemo test ako je danas neradni dan (nedelja) — testiramo samo logiku roka
    if (! in_array($today->dayOfWeek, config('booking.working_days'))) {
        Carbon::setTestNow(); // reset
        $this->markTestSkipped('Danas je neradni dan, test roka nije primenjiv.');
    }

    $slots = (new SlotFinder())->availableSlots($today, $service, null, $salon->id);

    // min_notice_minutes = 60, "sada" je 10:00 → 10:30 je premalo unapred, 11:00 je OK
    expect($slots)->not->toContain('10:00');
    expect($slots)->not->toContain('10:30');
    expect($slots)->toContain('11:00');

    Carbon::setTestNow(); // reset na pravo vreme, važno za ostale testove
});