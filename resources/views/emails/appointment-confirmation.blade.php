<x-mail::message>
# Potvrdi svoj termin

Zdravo {{ $appointment->customer_name }},

Rezervisao si termin kod **Barber Boki**:

- **Usluga:** {{ $appointment->service->name }}
- **Datum:** {{ $appointment->date->format('d.m.Y') }}
- **Vreme:** {{ $appointment->start_time }}

Klikni dugme ispod da potvrdiš termin. Link važi **15 minuta** — ako ne potvrdiš na vreme, termin će biti automatski otkazan.

<x-mail::button :url="$confirmUrl">
Potvrdi termin
</x-mail::button>

Hvala,<br>
Barber Boki
</x-mail::message>