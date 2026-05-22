<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sensor extends Model
{
    protected $fillable = [
        'device_id',
        'name',
        'type',
        'pin_number',
        'status',
        'threshold_min',
        'threshold_max',
        'unit',
        'sampling_rate',
        'calibration_data',
        'description',
        'is_active'
    ];

    protected $casts = [
        'calibration_data' => 'array',
        'threshold_min' => 'decimal:2',
        'threshold_max' => 'decimal:2',
        'is_active' => 'boolean',
        'sampling_rate' => 'integer'
    ];

    // Relasi ke device
    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
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

    // Scope untuk sensor aktif
    public function scopeActive($query)
    {
        return $query->where('status', 'active')->where('is_active', true);
    }

    // Scope berdasarkan tipe sensor
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    // Accessor untuk status color
    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'active' => 'green',
            'inactive' => 'red',
            'error' => 'red',
            default => 'gray'
        };
    }

    // Accessor untuk type display name
    public function getTypeDisplayAttribute()
    {
        return match($this->type) {
            'pir' => 'PIR Motion Sensor',
            'vibration' => 'Vibration Sensor (SW-420)',
            'reed_switch' => 'Reed Switch',
            'temperature' => 'Temperature Sensor',
            'humidity' => 'Humidity Sensor',
            default => ucfirst($this->type)
        };
    }

    // Method untuk mendapatkan reading terakhir
    public function getLatestReading()
    {
        return $this->sensorReadings()->latest('reading_time')->first();
    }
}
