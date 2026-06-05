<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'email', 'password',
        'last_name', 'first_name', 'middle_name',
        'group_id',
        'is_jobgiver', 'is_moderator', 'is_admin',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'is_jobgiver' => 'boolean',
        'is_moderator'    => 'boolean',
        'is_admin'   => 'boolean',
    ];

    public function isFreelancer(): bool
    {
        return !$this->is_jobgiver && !$this->is_moderator && !$this->is_admin;
    }

    public function isJobgiver(): bool { return (bool) $this->is_jobgiver; }
    public function isModerator(): bool    { return (bool) $this->is_moderator; }
    public function isAdmin(): bool   { return (bool) $this->is_admin; }

    public function roleLabel(): string
    {
        if ($this->is_admin)   return 'Администратор';
        if ($this->is_moderator)    return 'Деканат';
        if ($this->is_jobgiver) return 'Преподаватель';
        return 'Студент';
    }

    public function fullName(): string
    {
        return trim("{$this->last_name} {$this->first_name} {$this->middle_name}");
    }

    public function shortName(): string
    {
        $i = mb_substr($this->first_name, 0, 1);
        $o = $this->middle_name ? mb_substr($this->middle_name, 0, 1) . '.' : '';
        return "{$this->last_name} {$i}.{$o}";
    }

    // Relationships
    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function debts()
    {
        return $this->hasMany(Debt::class, 'freelancer_id');
    }

    public function assignedDebts()
    {
        return $this->hasMany(Debt::class, 'assigned_by_id');
    }

    public function disciplines()
    {
        return $this->belongsToMany(Discipline::class, 'discipline_jobgivers', 'jobgiver_id', 'discipline_id');
    }

    public function retakesAsJobgiver()
    {
        return $this->belongsToMany(Retake::class, 'retake_jobgivers', 'jobgiver_id', 'retake_id');
    }

    public function retakesAsFreelancer()
    {
        return $this->belongsToMany(Retake::class, 'retake_freelancers', 'freelancer_id', 'retake_id')
                    ->withPivot('result_status', 'grade_value', 'grade_scale', 'updated_at');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class)->orderByDesc('created_at');
    }

    public function unreadNotifications()
    {
        return $this->hasMany(Notification::class)->where('is_read', false);
    }

    public function jobgiverRoleRequests()
    {
        return $this->hasMany(JobgiverRoleRequest::class);
    }
}
