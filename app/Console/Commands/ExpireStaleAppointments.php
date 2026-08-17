<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use Illuminate\Console\Command;

class ExpireStaleAppointments extends Command
{
    protected $signature = 'appointments:expire-stale';

    protected $description = 'Označava kao expired sve pending termine kojima je istekao rok za potvrdu';

    public function handle(): void
    {
        $count = Appointment::where('status', 'pending')
            ->where('confirmation_expires_at', '<', now())
            ->update(['status' => 'expired']);

        $this->info("Označeno kao expired: {$count} termina.");
    }
}