<?php

use App\Models\Salon;
use App\Models\Staff;
use App\Models\Service;
use App\Models\Appointment;

it('can create a salon with staff, services and appointments', function () {
    $salon = Salon::create([
        'name' => 'Barber Boki',
        'slug' => 'barber-boki',
        'address' => 'Kralja Petra 12, Novi Sad',
        'phone' => '021/123-456',
    ]);

    $staff = Staff::create([
        'salon_id' => $salon->id,
        'name' => 'Boki',
    ]);

    $service = Service::create([
        'salon_id' => $salon->id,
        'name' => 'Šišanje + Brada',
        'price' => 1200,
        'duration_minutes' => 30,
    ]);

    $appointment = Appointment::create([
        'salon_id' => $salon->id,
        'staff_id' => $staff->id,
        'service_id' => $service->id,
        'customer_name' => 'Marko Marković',
        'customer_email' => 'marko@example.com',
        'customer_phone' => '064/123-456',
        'date' => '2026-08-19',
        'start_time' => '10:30',
        'end_time' => '11:00',
    ]);

    expect($salon->staff)->toHaveCount(1);
    expect($salon->services)->toHaveCount(1);
    expect($salon->appointments)->toHaveCount(1);
    expect($appointment->staff->name)->toBe('Boki');
    expect($appointment->service->price)->toBe(1200);
});