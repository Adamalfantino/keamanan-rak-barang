<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class DoorReading extends Model
{
    protected $fillable = [
        'device_id',
        'door_open',
        'is_authorized_access',
        'is_forced_entry',
        'access_type',
        'door_location',
        'open_duration_seconds',
        'proper_closure',
        'access_card_data',
        'metadata',
        'door_opened_at',
        'door_closed_at',
        'recorded_at'
    ];

    protected $casts = [
        'door_open' => 'boolean',
        'is_authorized_access' => 'boolean',
        'is_forced_entry' => 'boolean',
        'proper_closure' => 'boolean',
        'access_card_data' => 'array',
        'metadata' => 'array',
        'door_opened_at' => 'datetime',
        'door_closed_at' => 'datetime',
        'recorded_at' => 'datetime',
        'open_duration_seconds' => 'integer'
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    /**
     * Cek apakah akses dalam jam kerja yang sah
     */
    public function checkAuthorizedAccess(Carbon $timestamp = null): bool
    {
        $time = $timestamp ?? $this->recorded_at ?? now();
        
        // Jam kerja: Senin-Jumat 07:00-18:00 (lebih fleksibel untuk maintenance)
        $workStart = 7;  // 07:00
        $workEnd = 18;   // 18:00
        
        $hour = $time->hour;
        $isWeekday = $time->isWeekday();
        
        return $isWeekday && $hour >= $workStart && $hour < $workEnd;
    }

    /**
     * Tentukan apakah pembukaan paksa berdasarkan durasi dan kondisi
     */
    public function detectForcedEntry(): bool
    {
        // Indikator pembukaan paksa:
        // 1. Dibuka di luar jam kerja tanpa kartu akses
        // 2. Durasi terbuka sangat singkat (<5 detik) - kemungkinan dipaksa
        // 3. Tidak ditutup dengan benar
        // 4. Tidak ada data kartu akses di luar jam kerja
        
        if (!$this->is_authorized_access && !$this->access_card_data) {
            return true;
        }
        
        if ($this->open_duration_seconds > 0 && $this->open_duration_seconds < 5) {
            return true;
        }
        
        if (!$this->proper_closure && !$this->is_authorized_access) {
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
            return 'forced';
        }
        
        if (!$this->is_authorized_access) {
            return 'unauthorized';
        }
        
        // Cek apakah maintenance berdasarkan durasi dan waktu
        if ($this->open_duration_seconds > 300 && $this->is_authorized_access) {
            return 'maintenance';
        }
        
        return 'normal';
    }

    /**
     * Hitung durasi terbuka jika ada door_opened_at dan door_closed_at
     */
    public function calculateOpenDuration(): int
    {
        if ($this->door_opened_at && $this->door_closed_at) {
            return $this->door_opened_at->diffInSeconds($this->door_closed_at);
        }
        
        return $this->open_duration_seconds;
    }

    /**
     * Cek apakah pintu masih terbuka (belum ditutup)
     */
    public function isCurrentlyOpen(): bool
    {
        return $this->door_open && !$this->door_closed_at;
    }

    /**
     * Get security risk level
     */
    public function getSecurityRiskLevel(): string
    {
        if ($this->is_forced_entry) {
            return 'critical';
        }
        
        if (!$this->is_authorized_access) {
            return 'high';
        }
        
        if ($this->open_duration_seconds > 600) { // Terbuka > 10 menit
            return 'medium';
        }
        
        return 'low';
    }

    /**
     * Scope untuk pintu terbuka
     */
    public function scopeOpen($query)
    {
        return $query->where('door_open', true);
    }

    /**
     * Scope untuk akses tidak sah
     */
    public function scopeUnauthorized($query)
    {
        return $query->where('is_authorized_access', false);
    }

    /**
     * Scope untuk pembukaan paksa
     */
    public function scopeForcedEntry($query)
    {
        return $query->where('is_forced_entry', true);
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
     * Scope untuk pintu yang masih terbuka
     */
    public function scopeCurrentlyOpen($query)
    {
        return $query->where('door_open', true)->whereNull('door_closed_at');
    }
}
