<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SensorReading extends Model
{
    protected $fillable = [
        'sensor_id',
        'device_id',
        'value',
        'raw_value',
        'status',
        'battery_level',
        'signal_strength',
        'metadata',
        'reading_time',
        'is_processed'
    ];

    protected $casts = [
        'metadata' => 'array',
        'value' => 'decimal:4',
        'battery_level' => 'decimal:2',
        'signal_strength' => 'integer',
        'reading_time' => 'datetime',
        'is_processed' => 'boolean'
    ];

    // Relasi ke sensor
    public function sensor(): BelongsTo
    {
        return $this->belongsTo(Sensor::class);
    }

    // Relasi ke device
    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    // Scope untuk reading hari ini
    public function scopeToday($query)
    {
        return $query->whereDate('reading_time', today());
    }

    // Scope untuk reading dalam rentang waktu
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('reading_time', [$startDate, $endDate]);
    }

    // Scope berdasarkan status
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    // Accessor untuk status color
    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'normal' => 'green',
            'warning' => 'yellow',
            'critical' => 'red',
            'error' => 'red',
            default => 'gray'
        };
    }

    // Accessor untuk formatted value
    public function getFormattedValueAttribute()
    {
        $unit = $this->sensor->unit ?? '';
        return $this->value . ($unit ? ' ' . $unit : '');
    }
}
