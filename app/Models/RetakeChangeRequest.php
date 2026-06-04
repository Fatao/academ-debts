<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RetakeChangeRequest extends Model
{
    protected $fillable = [
        'retake_id', 'requested_by_id',
        'new_building', 'new_room',
        'new_start_datetime', 'new_duration_minutes',
        'comment', 'status', 'dean_comment', 'reviewed_by_id',
    ];

    protected $casts = [
        'new_start_datetime' => 'datetime',
        'reviewed_at'        => 'datetime',
    ];

    const STATUS_LABELS = [
        'PENDING'  => 'На рассмотрении',
        'APPROVED' => 'Одобрена',
        'REJECTED' => 'Отклонена',
    ];

    public function retake()
    {
        return $this->belongsTo(Retake::class);
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by_id');
    }

    public function reviewedBy()
    {
        return $this->belongsTo(User::class, 'reviewed_by_id');
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }
}