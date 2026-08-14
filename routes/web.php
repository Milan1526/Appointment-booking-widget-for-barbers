<?php

use Illuminate\Support\Facades\Route;
use App\Models\Appointment;

Route::get('/', function () {
    return redirect()->route('booking');
});

Route::get('/zakazivanje', function () {
    return view('booking');
})->name('booking');

Route::get('/potvrdi/{token}', function (string $token) {
    $appointment = Appointment::where('confirmation_token', $token)->firstOrFail();

    if ($appointment->status !== 'pending') {
        return view('confirmation-result', ['success' => false, 'message' => 'Ovaj termin je već obrađen ili je istekao.']);
    }

    if ($appointment->confirmation_expires_at->isPast()) {
        $appointment->update(['status' => 'expired']);
        return view('confirmation-result', ['success' => false, 'message' => 'Rok za potvrdu je istekao. Termin je otkazan, možeš zakazati novi.']);
    }

    $appointment->update(['status' => 'confirmed']);

    return view('confirmation-result', ['success' => true, 'message' => 'Termin je potvrđen! Vidimo se uskoro.']);
})->name('appointments.confirm');
