<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Service;
use App\Models\Staff;
use Carbon\Carbon;

class SlotFinder
{
    /**
     * Vraća listu slobodnih termina (kao "H:i" stringove) za dati dan, uslugu i (opciono) majstora.
     */
    public function availableSlots(Carbon $date, Service $service, ?int $staffId, int $salonId): array
    {
        $workingDays = config('booking.working_days');

        // Salon ne radi tog dana (npr. nedelja)
        if (! in_array($date->dayOfWeek, $workingDays)) {
            return [];
        }

        // Koje majstore proveravamo — jednog izabranog, ili sve aktivne (za "Bilo ko")
        $staffMembers = $staffId
            ? Staff::where('id', $staffId)->get()
            : Staff::where('salon_id', $salonId)->where('is_active', true)->get();

        if ($staffMembers->isEmpty()) {
            return [];
        }

        $interval = config('booking.slot_interval_minutes');
        $workStart = Carbon::parse($date->toDateString() . ' ' . config('booking.working_hours.start'));
        $workEnd = Carbon::parse($date->toDateString() . ' ' . config('booking.working_hours.end'));

        $duration = $service->duration_minutes;

        // Unapred učitaj sve postojeće termine tog dana za sve relevantne majstore (1 upit umesto N)
        $existingAppointments = Appointment::whereIn('staff_id', $staffMembers->pluck('id'))
            ->whereDate('date', $date->toDateString())
            ->whereIn('status', ['pending', 'confirmed'])
            ->get();

        $slots = [];
        $slotStart = $workStart->copy();

        while ($slotStart->copy()->addMinutes($duration)->lte($workEnd)) {
            $slotEnd = $slotStart->copy()->addMinutes($duration);

            // Slot je slobodan ako BAR JEDAN od proveravanih majstora nema preklapanje
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