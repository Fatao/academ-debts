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
        'is_teacher', 'is_dean', 'is_admin',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'is_teacher' => 'boolean',
        'is_dean'    => 'boolean',
        'is_admin'   => 'boolean',
    ];

    public function isStudent(): bool
    {
        return !$this->is_teacher && !$this->is_dean && !$this->is_admin;
    }

    public function isTeacher(): bool { return (bool) $this->is_teacher; }
    public function isDean(): bool    { return (bool) $this->is_dean; }
    public function isAdmin(): bool   { return (bool) $this->is_admin; }

    public function roleLabel(): string
    {
        if ($this->is_admin)   return 'Администратор';
        if ($this->is_dean)    return 'Деканат';
        if ($this->is_teacher) return 'Преподаватель';
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
        return $this->hasMany(Debt::class, 'student_id');
    }

    public function assignedDebts()
    {
        return $this->hasMany(Debt::class, 'assigned_by_id');
    }

    public function disciplines()
    {
        return $this->belongsToMany(Discipline::class, 'discipline_teachers', 'teacher_id', 'discipline_id');
    }

    public function retakesAsTeacher()
    {
        return $this->belongsToMany(Retake::class, 'retake_teachers', 'teacher_id', 'retake_id');
    }

    public function retakesAsStudent()
    {
        return $this->belongsToMany(Retake::class, 'retake_students', 'student_id', 'retake_id')
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

    public function teacherRoleRequests()
    {
        return $this->hasMany(TeacherRoleRequest::class);
    }
}