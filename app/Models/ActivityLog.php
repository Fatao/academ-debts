<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id', 'action', 'entity_type', 'entity_id', 'payload',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'payload'    => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function record(
        string $action,
        string $entityType,
        int $entityId,
        array $payload = [],
        ?int $userId = null
    ): void {
        self::create([
            'user_id'     => $userId ?? auth()->id(),
            'action'      => $action,
            'entity_type' => $entityType,
            'entity_id'   => $entityId,
            'payload'     => $payload ?: null,
        ]);
    }
}