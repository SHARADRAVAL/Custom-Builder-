<?php

// namespace App\Models;

// use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\Model;

// class Task extends Model
// {
//     use HasFactory;

//     protected $fillable = [
//         'title',
//         'description',
//         'start_time',
//         'end_time',
//         'user_id',
//         'status',
//         'reminder_sent',
//         'started_at',
//         'completed_at',
//     ];

//     protected $casts = [
//         'start_time' => 'datetime',
//         'end_time' => 'datetime',
//         'started_at' => 'datetime',
//         'completed_at' => 'datetime',
//         'reminder_sent' => 'boolean',
//     ];

//     // Task belongs to a user
//     public function user()
//     {
//         return $this->belongsTo(User::class);
//     }
// }

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'user_id',
        'start_time',
        'end_time',
        'status',
        'repeat_type',
        'reminder_sent',
        'started_at',
        'completed_at'
    ];

    public function recurring()
    {
        return $this->hasOne(RecurringTask::class);
    }
    public function notes()
    {
        return $this->hasMany(Note::class);
    }


    // Task belongs to a user
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
