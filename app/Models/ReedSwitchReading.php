<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class ReedSwitchReading extends Model
{
    protected $fillable = [
        'device_id',
        'door_open',
        'is_authorized',
        'is_forced_entry',
        'access_method',
        'door_status',
        'open_duration_seconds',
        'access_level',
        'door_location',
        'metadata',
        'door_opened_at',
        'door_closed_at',
        'recorded_at'
    ];

    protected $casts = [
        'door_open' => 'boolean',
        'is_authorized' => 'boolean',
        'is_forced_entry' => 'boolean',
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
     * Cek apakah akses dalam jam kerja yang diizinkan
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
     * Tentukan apakah akses tidak sah berdasarkan berbagai faktor
     */
    public function determineUnauthorizedAccess(): bool
    {
        // Akses tidak sah jika:
        // 1. Di luar jam kerja
        // 2. Pembukaan paksa (forced entry)
        // 3. Durasi terbuka terlalu lama (>1800 detik = 30 menit)
        // 4. Metode akses tidak dikenal
        
        if (!$this->checkAuthorizedTime()) {
            return true;
        }
        
        if ($this->is_forced_entry) {
            return true;
        }
        
        if ($this->open_duration_seconds > 1800) { // 30 menit
            return true;
        }
        
        if ($this->access_method === 'unknown' || $this->access_method === 'force') {
            return true;
        }
        
        return false;
    }

    /**
     * Tentukan level akses berdasarkan kondisi
     */
    public function determineAccessLevel(): string
    {
        if ($this->is_forced_entry) {
            return 'emergency';
        }
        
        if (!$this->checkAuthorizedTime()) {
            return 'unauthorized';
        }
        
        if ($this->open_duration_seconds > 1800 || $this->access_method === 'unknown') {
            return 'suspicious';
        }
        
        return 'normal';
    }

    /**
     * Tentukan status pintu berdasarkan kondisi
     */
    public function determineDoorStatus(): string
    {
        if ($this->is_forced_entry) {
            return 'forced';
        }
        
        if (!$this->door_open) {
            return 'closed';
        }
        
        if ($this->open_duration_seconds > 300) { // 5 menit
            return 'ajar'; // Pintu terbuka terlalu lama
        }
        
        return 'open';
    }

    /**
     * Hitung durasi pintu terbuka jika ada door_opened_at dan door_closed_at
     */
    public function calculateOpenDuration(): int
    {
        if ($this->door_opened_at && $this->door_closed_at) {
            return $this->door_opened_at->diffInSeconds($this->door_closed_at);
        }
        
        if ($this->door_opened_at && $this->door_open) {
            // Pintu masih terbuka, hitung dari sekarang
            return $this->door_opened_at->diffInSeconds(now());
        }
        
        return $this->open_duration_seconds;
    }

    /**
     * Cek apakah ini pembukaan paksa berdasarkan pola
     */
    public function detectForcedEntry(): bool
    {
        // Deteksi pembukaan paksa berdasarkan:
        // 1. Tidak ada metode akses yang valid
        // 2. Pembukaan di luar jam kerja dengan durasi pendek (hit and run)
        // 3. Multiple attempts dalam waktu singkat
        
        if ($this->access_method === 'force' || $this->access_method === 'unknown') {
            return true;
        }
        
        if (!$this->checkAuthorizedTime() && $this->open_duration_seconds < 30) {
            return true; // Pembukaan cepat di luar jam kerja
        }
        
        return false;
    }

    /**
     * Scope untuk pintu terbuka
     */
    public function scopeDoorOpen($query)
    {
        return $query->where('door_open', true);
    }

    /**
     * Scope untuk akses tidak sah
     */
    public function scopeUnauthorized($query)
    {
        return $query->where('is_authorized', false);
    }

    /**
     * Scope untuk pembukaan paksa
     */
    public function scopeForcedEntry($query)
    {
        return $query->where('is_forced_entry', true);
    }

    /**
     * Scope untuk level akses tertentu
     */
    public function scopeAccessLevel($query, $level)
    {
        return $query->where('access_level', $level);
    }

    /**
     * Scope untuk lokasi pintu tertentu
     */
    public function scopeDoorLocation($query, $location)
    {
        return $query->where('door_location', $location);
    }

    /**
     * Scope untuk status pintu tertentu
     */
    public function scopeDoorStatus($query, $status)
    {
        return $query->where('door_status', $status);
    }

    /**
     * Scope untuk metode akses tertentu
     */
    public function scopeAccessMethod($query, $method)
    {
        return $query->where('access_method', $method);
    }
}
