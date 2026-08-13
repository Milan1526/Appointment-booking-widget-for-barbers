<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Appointment extends Model
{
    protected $fillable = [
        'salon_id', 'staff_id', 'service_id',
        'customer_name', 'customer_email', 'customer_phone',
        'date', 'start_time', 'end_time',
        'status', 'confirmation_token', 'confirmation_expires_at',
    ];

    protected $casts = [
        'date' => 'date',
        'confirmation_expires_at' => 'datetime',
    ];

    public function salon(): BelongsTo
    {
        return $this->belongsTo(Salon::class);
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}