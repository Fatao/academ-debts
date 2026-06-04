<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id', 'type', 'title', 'message',
        'related_retake_id', 'related_debt_id', 'is_read',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'is_read'    => 'boolean',
    ];

    const TYPE_RETAKE_ASSIGNED  = 'RETAKE_ASSIGNED';
    const TYPE_RETAKE_UPDATED   = 'RETAKE_UPDATED';
    const TYPE_DEBT_CREATED     = 'DEBT_CREATED';
    const TYPE_DEBT_CLOSED      = 'DEBT_CLOSED';
    const TYPE_REQUEST_APPROVED = 'REQUEST_APPROVED';
    const TYPE_REQUEST_REJECTED = 'REQUEST_REJECTED';

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function relatedRetake()
    {
        return $this->belongsTo(Retake::class, 'related_retake_id');
    }

    public function relatedDebt()
    {
        return $this->belongsTo(Debt::class, 'related_debt_id');
    }

    public static function send(
        int $userId,
        string $type,
        string $title,
        string $message,
        array $extra = []
    ): self {
        return self::create(array_merge([
            'user_id' => $userId,
            'type'    => $type,
            'title'   => $title,
            'message' => $message,
            'is_read' => false,
        ], $extra));
    }
}