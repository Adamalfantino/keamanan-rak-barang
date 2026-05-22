<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Alert extends Model
{
    protected $fillable = [
        'device_id',
        'sensor_id',
        'activity_log_id',
        'alert_type',
        'priority',
        'status',
        'title',
        'message',
        'alert_data',
        'location',
        'triggered_at',
        'acknowledged_at',
        'resolved_at',
        'acknowledged_by',
        'resolved_by',
        'resolution_notes',
        'is_sent_notification',
        'notification_channels',
    ];

    protected $casts = [
        'alert_data' => 'array',
        'notification_channels' => 'array',
        'triggered_at' => 'datetime',
        'acknowledged_at' => 'datetime',
        'resolved_at' => 'datetime',
        'is_sent_notification' => 'boolean',
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

    // Relasi ke activity log
    public function activityLog(): BelongsTo
    {
        return $this->belongsTo(ActivityLog::class);
    }

    // Scope untuk alert aktif
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    // Scope berdasarkan priority
    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    // Scope untuk alert hari ini
    public function scopeToday($query)
    {
        return $query->whereDate('triggered_at', today());
    }

    // Scope untuk alert yang belum diakui
    public function scopeUnacknowledged($query)
    {
        return $query->where('status', 'active');
    }

    // Accessor untuk priority color
    public function getPriorityColorAttribute()
    {
        switch ($this->priority) {
            case 'low':
                return 'green';
            case 'medium':
                return 'yellow';
            case 'high':
                return 'orange';
            case 'critical':
                return 'red';
            default:
                return 'gray';
        }
    }

    // Accessor untuk status color
    public function getStatusColorAttribute()
    {
        switch ($this->status) {
            case 'active':
                return 'red';
            case 'acknowledged':
                return 'yellow';
            case 'resolved':
                return 'green';
            case 'dismissed':
                return 'gray';
            default:
                return 'gray';
        }
    }

    // Accessor untuk alert type display
    public function getAlertTypeDisplayAttribute()
    {
        switch ($this->alert_type) {
            case 'security_breach':
                return 'Pelanggaran Keamanan';
            case 'device_offline':
                return 'Device Offline';
            case 'sensor_offline':
                return 'Sensor Offline';
            case 'low_battery':
                return 'Baterai Lemah';
            case 'high_vibration':
                return 'Getaran Tinggi';
            case 'unauthorized_access':
                return 'Akses Tidak Sah';
            default:
                return ucfirst(str_replace('_', ' ', $this->alert_type));
        }
    }

    // Method untuk acknowledge alert
    public function acknowledge($acknowledgedBy = 'system', $notes = null)
    {
        $this->update([
            'status' => 'acknowledged',
            'acknowledged_at' => now(),
            'acknowledged_by' => $acknowledgedBy,
            'resolution_notes' => $notes
        ]);
    }

    // Method untuk resolve alert
    public function resolve($resolvedBy = 'system', $notes = null)
    {
        $this->update([
            'status' => 'resolved',
            'resolved_at' => now(),
            'resolved_by' => $resolvedBy,
            'resolution_notes' => $notes,
        ]);
    }

    // Method untuk dismiss alert
    public function dismiss($dismissedBy = 'system', $notes = null)
    {
        $this->update([
            'status' => 'dismissed',
            'resolved_at' => now(),
            'resolved_by' => $dismissedBy,
            'resolution_notes' => $notes
        ]);
    }
}
