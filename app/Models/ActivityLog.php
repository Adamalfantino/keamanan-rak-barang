<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ActivityLog extends Model
{
    protected $fillable = [
        'device_id',
        'sensor_id',
        'event_type',
        'severity',
        'title',
        'description',
        'event_data',
        'location',
        'user_agent',
        'ip_address',
        'event_time',
        'is_acknowledged',
        'acknowledged_at',
        'acknowledged_by'
    ];

    protected $casts = [
        'event_data' => 'array',
        'event_time' => 'datetime',
        'acknowledged_at' => 'datetime',
        'is_acknowledged' => 'boolean'
    ];

    // Relasi ke device
    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    // Relasi ke sensor
    public function sensor(): BelongsTo
    {
        return $this->belongsTo(Sensor::class);
    }

    // Relasi ke alerts
    public function alerts(): HasMany
    {
        return $this->hasMany(Alert::class);
    }

    // Scope untuk log hari ini
    public function scopeToday($query)
    {
        return $query->whereDate('event_time', today());
    }

    // Scope berdasarkan severity
    public function scopeBySeverity($query, $severity)
    {
        return $query->where('severity', $severity);
    }

    // Scope untuk log yang belum diakui
    public function scopeUnacknowledged($query)
    {
        return $query->where('is_acknowledged', false);
    }

    // Scope berdasarkan event type
    public function scopeByEventType($query, $eventType)
    {
        return $query->where('event_type', $eventType);
    }

    // Accessor untuk severity color
    public function getSeverityColorAttribute()
    {
        return match($this->severity) {
            'info' => 'blue',
            'warning' => 'yellow',
            'critical' => 'red',
            'error' => 'red',
            default => 'gray'
        };
    }

    // Accessor untuk event type display
    public function getEventTypeDisplayAttribute()
    {
        return match($this->event_type) {
            'motion_detected' => 'Gerakan Terdeteksi',
            'door_opened' => 'Rak Dibuka',
            'door_closed' => 'Rak Ditutup',
            'vibration_detected' => 'Getaran Terdeteksi',
            'system_normal' => 'Sistem Normal',
            'device_offline' => 'Device Offline',
            'device_online' => 'Device Online',
            'low_battery' => 'Baterai Lemah',
            default => ucfirst(str_replace('_', ' ', $this->event_type))
        };
    }

    // Method untuk acknowledge log
    public function acknowledge($acknowledgedBy = 'system')
    {
        $this->update([
            'is_acknowledged' => true,
            'acknowledged_at' => now(),
            'acknowledged_by' => $acknowledgedBy
        ]);
    }
}
