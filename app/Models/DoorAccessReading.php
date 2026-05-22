<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class DoorAccessReading extends Model
{
    protected $fillable = [
        'device_id',
        'door_opened',
        'is_authorized_access',
        'access_type',
        'access_method',
        'user_id_card',
        'duration_seconds',
        'is_suspicious',
        'door_location',
        'is_forced_entry',
        'metadata',
        'door_opened_at',
        'door_closed_at',
        'recorded_at'
    ];

    protected $casts = [
        'door_opened' => 'boolean',
        'is_authorized_access' => 'boolean',
        'is_suspicious' => 'boolean',
        'is_forced_entry' => 'boolean',
        'metadata' => 'array',
        'door_opened_at' => 'datetime',
        'door_closed_at' => 'datetime',
        'recorded_at' => 'datetime',
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
        
        // Jam kerja: Senin-Jumat 07:00-18:00 (lebih fleksibel untuk akses pintu)
        $workStart = 7;  // 07:00
        $workEnd = 18;   // 18:00
        
        $hour = $time->hour;
        $isWeekday = $time->isWeekday();
        
        return $isWeekday && $hour >= $workStart && $hour < $workEnd;
    }

    /**
     * Tentukan apakah akses mencurigakan berdasarkan berbagai faktor
     */
    public function determineSuspiciousAccess(): bool
    {
        // Akses mencurigakan jika:
        // 1. Forced entry (paksa masuk)
        // 2. Akses di luar jam kerja tanpa ID card
        // 3. Durasi pintu terbuka terlalu lama (>180 detik)
        // 4. Akses berulang dalam waktu singkat
        // 5. Akses tanpa ID card di jam kerja
        
        if ($this->is_forced_entry) {
            return true;
        }
        
        if (!$this->checkAuthorizedTime() && !$this->user_id_card) {
            return true;
        }
        
        if ($this->duration_seconds > 180) {
            return true;
        }
        
        if (!$this->user_id_card && $this->access_method !== 'emergency') {
            return true;
        }
        
        return false;
    }

    /**
     * Tentukan tipe akses berdasarkan kondisi
     */
    public function determineAccessType(): string
    {
        if ($this->is_forced_entry) {
            return 'forced_entry';
        }
        
        if ($this->access_method === 'emergency') {
            return 'emergency';
        }
        
        if ($this->access_method === 'maintenance') {
            return 'maintenance';
        }
        
        if ($this->user_id_card && $this->checkAuthorizedTime()) {
            return 'authorized';
        }
        
        if (!$this->checkAuthorizedTime()) {
            return 'after_hours';
        }
        
        return 'unauthorized';
    }

    /**
     * Hitung durasi pintu terbuka jika ada door_opened_at dan door_closed_at
     */
    public function calculateDuration(): int
    {
        if ($this->door_opened_at && $this->door_closed_at) {
            return $this->door_opened_at->diffInSeconds($this->door_closed_at);
        }
        
        return $this->duration_seconds;
    }

    /**
     * Cek apakah ID card valid (simulasi - bisa diintegrasikan dengan sistem HR)
     */
    public function isValidIdCard(): bool
    {
        if (!$this->user_id_card) {
            return false;
        }
        
        // Simulasi validasi ID card
        // Format: EMP-XXXX (4 digit angka)
        return preg_match('/^EMP-\d{4}$/', $this->user_id_card);
    }

    /**
     * Get priority level berdasarkan tipe akses
     */
    public function getPriorityLevel(): string
    {
        return match($this->access_type) {
            'forced_entry' => 'critical',
            'unauthorized' => 'high',
            'after_hours' => 'high',
            'emergency' => 'medium',
            'maintenance' => 'low',
            'authorized' => 'info',
            default => 'medium'
        };
    }

    /**
     * Scope untuk pintu terbuka
     */
    public function scopeDoorOpened($query)
    {
        return $query->where('door_opened', true);
    }

    /**
     * Scope untuk akses mencurigakan
     */
    public function scopeSuspicious($query)
    {
        return $query->where('is_suspicious', true);
    }

    /**
     * Scope untuk akses tidak sah
     */
    public function scopeUnauthorized($query)
    {
        return $query->where('is_authorized_access', false);
    }

    /**
     * Scope untuk tipe akses tertentu
     */
    public function scopeAccessType($query, $type)
    {
        return $query->where('access_type', $type);
    }

    /**
     * Scope untuk lokasi pintu tertentu
     */
    public function scopeDoorLocation($query, $location)
    {
        return $query->where('door_location', $location);
    }

    /**
     * Scope untuk forced entry
     */
    public function scopeForcedEntry($query)
    {
        return $query->where('is_forced_entry', true);
    }

    /**
     * Get access summary untuk dashboard
     */
    public function getAccessSummary(): array
    {
        return [
            'id' => $this->id,
            'door_opened' => $this->door_opened,
            'access_type' => $this->access_type,
            'is_suspicious' => $this->is_suspicious,
            'user_id_card' => $this->user_id_card,
            'duration' => $this->duration_seconds,
            'location' => $this->door_location,
            'priority' => $this->getPriorityLevel(),
            'timestamp' => $this->recorded_at->format('Y-m-d H:i:s')
        ];
    }
}
