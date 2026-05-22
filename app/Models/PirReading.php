<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class PirReading extends Model
{
    protected $fillable = [
        'device_id',
        'motion_detected',
        'motion_intensity',
        'duration_seconds',
        'is_authorized_time',
        'is_suspicious',
        'motion_type',
        'detection_zone',
        'metadata',
        'motion_start',
        'motion_end',
        'recorded_at'
    ];

    protected $casts = [
        'motion_detected' => 'boolean',
        'is_authorized_time' => 'boolean',
        'is_suspicious' => 'boolean',
        'metadata' => 'array',
        'motion_start' => 'datetime',
        'motion_end' => 'datetime',
        'recorded_at' => 'datetime',
        'motion_intensity' => 'integer',
        'duration_seconds' => 'integer'
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    /**
     * Cek apakah waktu dalam jam kerja (authorized time)
     */
    public function checkAuthorizedTime(Carbon $timestamp = null): bool
    {
        $time = $timestamp ?? $this->recorded_at ?? now();
        
        // Jam kerja: Senin-Jumat 08:00-17:00
        $workStart = 8; // 08:00
        $workEnd = 17;  // 17:00
        
        $hour = $time->hour;
        $isWeekday = $time->isWeekday();
        
        return $isWeekday && $hour >= $workStart && $hour < $workEnd;
    }

    /**
     * Tentukan apakah gerakan mencurigakan
     */
    public function determineSuspiciousMotion(): bool
    {
        // Gerakan mencurigakan jika:
        // 1. Di luar jam kerja
        // 2. Intensitas tinggi (>70) di luar jam kerja
        // 3. Durasi sangat lama (>300 detik) di luar jam kerja
        // 4. Gerakan berulang dalam waktu singkat
        
        if (!$this->is_authorized_time) {
            return true;
        }
        
        if (!$this->is_authorized_time && $this->motion_intensity > 70) {
            return true;
        }
        
        if (!$this->is_authorized_time && $this->duration_seconds > 300) {
            return true;
        }
        
        return false;
    }

    /**
     * Tentukan tipe gerakan berdasarkan kondisi
     */
    public function determineMotionType(): string
    {
        if (!$this->motion_detected) {
            return 'none';
        }
        
        if (!$this->is_authorized_time) {
            return 'unauthorized';
        }
        
        if ($this->is_suspicious) {
            return 'suspicious';
        }
        
        if ($this->motion_intensity > 80 || $this->duration_seconds > 600) {
            return 'suspicious';
        }
        
        return 'normal';
    }

    /**
     * Hitung durasi gerakan jika ada motion_start dan motion_end
     */
    public function calculateDuration(): int
    {
        if ($this->motion_start && $this->motion_end) {
            return $this->motion_start->diffInSeconds($this->motion_end);
        }
        
        return $this->duration_seconds;
    }

    /**
     * Scope untuk gerakan terdeteksi
     */
    public function scopeMotionDetected($query)
    {
        return $query->where('motion_detected', true);
    }

    /**
     * Scope untuk gerakan mencurigakan
     */
    public function scopeSuspicious($query)
    {
        return $query->where('is_suspicious', true);
    }

    /**
     * Scope untuk gerakan di luar jam kerja
     */
    public function scopeUnauthorizedTime($query)
    {
        return $query->where('is_authorized_time', false);
    }

    /**
     * Scope untuk tipe gerakan tertentu
     */
    public function scopeMotionType($query, $type)
    {
        return $query->where('motion_type', $type);
    }

    /**
     * Scope untuk zona deteksi tertentu
     */
    public function scopeDetectionZone($query, $zone)
    {
        return $query->where('detection_zone', $zone);
    }
}
