<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Discipline extends Model
{
    protected $fillable = ['code', 'name'];

    public function teachers()
    {
        return $this->belongsToMany(User::class, 'discipline_teachers', 'discipline_id', 'teacher_id');
    }

    public function debts()
    {
        return $this->hasMany(Debt::class);
    }

    public function retakes()
    {
        return $this->hasMany(Retake::class);
    }
}