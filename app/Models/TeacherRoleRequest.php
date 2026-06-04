<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherRoleRequest extends Model
{
    protected $fillable = [
        'user_id', 'status', 'comment', 'dean_comment', 'reviewed_by_id',
    ];

    protected $casts = ['reviewed_at' => 'datetime'];

    const STATUS_LABELS = [
        'PENDING'  => 'На рассмотрении',
        'APPROVED' => 'Одобрена',
        'REJECTED' => 'Отклонена',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
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