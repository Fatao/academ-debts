<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Retake extends Model
{
    protected $fillable = [
        'discipline_id', 'type',
        'building_number', 'room_number',
        'start_datetime', 'duration_minutes',
        'status', 'created_by_id',
    ];

    protected $casts = ['start_datetime' => 'datetime'];

    const STATUS_SCHEDULED   = 'SCHEDULED';
    const STATUS_IN_PROGRESS = 'IN_PROGRESS';
    const STATUS_COMPLETED   = 'COMPLETED';

    const TYPE_REGULAR    = 'REGULAR';
    const TYPE_COMMISSION = 'COMMISSION';

    const STATUS_LABELS = [
        'SCHEDULED'   => 'Назначена',
        'IN_PROGRESS' => 'Проводится',
        'COMPLETED'   => 'Завершена',
    ];

    const TYPE_LABELS = [
        'REGULAR'    => 'Обычная',
        'COMMISSION' => 'С комиссией',
    ];

    public function discipline()
    {
        return $this->belongsTo(Discipline::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function teachers()
    {
        return $this->belongsToMany(User::class, 'retake_teachers', 'retake_id', 'teacher_id');
    }

    public function students()
    {
        return $this->belongsToMany(User::class, 'retake_students', 'retake_id', 'student_id')
                    ->withPivot('result_status', 'grade_value', 'grade_scale', 'updated_by_id', 'updated_at');
    }

    public function debts()
    {
        return $this->belongsToMany(Debt::class, 'retake_debts');
    }

    public function changeRequests()
    {
        return $this->hasMany(RetakeChangeRequest::class);
    }

    public function endsAt(): Carbon
    {
        return $this->start_datetime->copy()->addMinutes($this->duration_minutes);
    }

    public function syncStatus(): void
    {
        $now = now();
        if ($now >= $this->endsAt()) {
            $resolved = self::STATUS_COMPLETED;
        } elseif ($now >= $this->start_datetime) {
            $resolved = self::STATUS_IN_PROGRESS;
        } else {
            $resolved = self::STATUS_SCHEDULED;
        }

        if ($this->status !== $resolved) {
            $this->update(['status' => $resolved]);
        }
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    public function typeLabel(): string
    {
        return self::TYPE_LABELS[$this->type] ?? $this->type;
    }

    public function isCommission(): bool
    {
        return $this->type === self::TYPE_COMMISSION;
    }

    public function location(): string
    {
        return "Корп. {$this->building_number}, ауд. {$this->room_number}";
    }
}