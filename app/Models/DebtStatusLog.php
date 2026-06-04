<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DebtStatusLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'debt_id', 'old_status', 'new_status', 'changed_by_id',
    ];

    protected $casts = ['changed_at' => 'datetime'];

    public function debt()
    {
        return $this->belongsTo(Debt::class);
    }

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by_id');
    }
}