<?php

namespace App\Mail;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AppointmentConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Appointment $appointment)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Potvrdi svoj termin - Barber Boki',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.appointment-confirmation',
            with: [
                'confirmUrl' => route('appointments.confirm', $this->appointment->confirmation_token),
            ],
        );
    }
}