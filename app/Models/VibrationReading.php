<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VibrationReading extends Model
{
    protected $fillable = [
        'device_id',
        'x_axis',
        'y_axis', 
        'z_axis',
        'magnitude',
        'is_abnormal',
        'threshold',
        'status',
        'metadata',
        'recorded_at'
    ];

    protected $casts = [
        'is_abnormal' => 'boolean',
        'metadata' => 'array',
        'recorded_at' => 'datetime',
        'x_axis' => 'float',
        'y_axis' => 'float',
        'z_axis' => 'float',
        'magnitude' => 'float',
        'threshold' => 'float'
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    /**
     * Hitung magnitude getaran dari sumbu X, Y, Z
     */
    public function calculateMagnitude(): float
    {
        return sqrt(pow($this->x_axis, 2) + pow($this->y_axis, 2) + pow($this->z_axis, 2));
    }

    /**
     * Cek apakah getaran abnormal berdasarkan threshold
     */
    public function checkAbnormal(): bool
    {
        $magnitude = $this->calculateMagnitude();
        return $magnitude > $this->threshold;
    }

    /**
     * Tentukan status berdasarkan magnitude
     */
    public function determineStatus(): string
    {
        $magnitude = $this->calculateMagnitude();
        
        if ($magnitude <= $this->threshold) {
            return 'normal';
        } elseif ($magnitude <= $this->threshold * 1.5) {
            return 'warning';
        } else {
            return 'critical';
        }
    }

    /**
     * Scope untuk getaran abnormal
     */
    public function scopeAbnormal($query)
    {
        return $query->where('is_abnormal', true);
    }

    /**
     * Scope untuk status tertentu
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }
}
