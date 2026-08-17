<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Service;
use App\Models\Staff;
use Carbon\Carbon;

class SlotFinder
{
    public function availableSlots(Carbon $date, Service $service, ?int $staffId, int $salonId): array
    {
        $workingDays = config('booking.working_days');

        if (! in_array($date->dayOfWeek, $workingDays)) {
            return [];
        }

        $staffMembers = $staffId
            ? Staff::where('id', $staffId)->get()
            : Staff::where('salon_id', $salonId)->where('is_active', true)->get();

        if ($staffMembers->isEmpty()) {
            return [];
        }

        $interval = config('booking.slot_interval_minutes');
        $workStart = Carbon::parse($date->toDateString() . ' ' . config('booking.working_hours.start'));
        $workEnd = Carbon::parse($date->toDateString() . ' ' . config('booking.working_hours.end'));

        // Najraniji dozvoljen trenutak za start termina — sprečava zakazivanje u prošlosti ili "za 5 minuta"
        $earliestAllowed = Carbon::now()->addMinutes(config('booking.min_notice_minutes'));

        $duration = $service->duration_minutes;

        $existingAppointments = Appointment::whereIn('staff_id', $staffMembers->pluck('id'))
            ->whereDate('date', $date->toDateString())
            ->whereIn('status', ['pending', 'confirmed'])
            ->get();

        $slots = [];
        $slotStart = $workStart->copy();

        while ($slotStart->copy()->addMinutes($duration)->lte($workEnd)) {
            $slotEnd = $slotStart->copy()->addMinutes($duration);

            // Preskoči slotove koji su prerani (prošlost ili premalo unapred)
            if ($slotStart->lt($earliestAllowed)) {
                $slotStart->addMinutes($interval);
                continue;
            }

            $isFreeForAtLeastOneStaff = $staffMembers->contains(function (Staff $staff) use ($existingAppointments, $slotStart, $slotEnd) {
                return ! $existingAppointments
                    ->where('staff_id', $staff->id)
                    ->contains(function (Appointment $appointment) use ($slotStart, $slotEnd) {
                        $apptStart = Carbon::parse($appointment->date->toDateString() . ' ' . $appointment->start_time);
                        $apptEnd = Carbon::parse($appointment->date->toDateString() . ' ' . $appointment->end_time);

                        return $slotStart->lt($apptEnd) && $slotEnd->gt($apptStart);
                    });
            });

            if ($isFreeForAtLeastOneStaff) {
                $slots[] = $slotStart->format('H:i');
            }

            $slotStart->addMinutes($interval);
        }

        return $slots;
    }
}