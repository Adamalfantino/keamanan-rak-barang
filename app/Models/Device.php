<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Device extends Model
{
    protected $fillable = [
        'name',
        'device_id',
        'location',
        'type',
        'status',
        'ip_address',
        'mac_address',
        'signal_strength',
        'last_seen',
        'configuration',
        'description',
        'is_active'
    ];

    protected $casts = [
        'configuration' => 'array',
        'last_seen' => 'datetime',
        'is_active' => 'boolean',
        'signal_strength' => 'integer'
    ];

    // Relasi ke sensors
    public function sensors(): HasMany
    {
        return $this->hasMany(Sensor::class);
    }

    // Relasi ke sensor readings
    public function sensorReadings(): HasMany
    {
        return $this->hasMany(SensorReading::class);
    }

    // Relasi ke activity logs
    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    // Relasi ke alerts
    public function alerts(): HasMany
    {
        return $this->hasMany(Alert::class);
    }

    // Scope untuk device online
    public function scopeOnline($query)
    {
        return $query->where('status', 'online');
    }

    // Scope untuk device aktif
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Accessor untuk status badge color
    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'online' => 'green',
            'offline' => 'red',
            'maintenance' => 'yellow',
            default => 'gray'
        };
    }
}
