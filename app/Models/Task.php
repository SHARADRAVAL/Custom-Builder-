<?php

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
        'due_days',
        'status',
        'repeat_type',
        'reminder_sent',
        'started_at',
        'completed_at',
        'comment',   
        'feedback',  
        'parent_task_id', // NEW
    ];

    protected $casts = [
        'start_time'    => 'datetime',
        'end_time'      => 'datetime',
        'started_at'    => 'datetime',
        'completed_at'  => 'datetime',
        'reminder_sent' => 'boolean',
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime',
    ];

    public function recurring()
    {
        return $this->hasOne(RecurringTask::class);
    }

    public function notes()
    {
        return $this->hasMany(Note::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'task_user');
    }

    /**
     * Relationship: Parent Task (Recurring Template)
     */
    public function parentTask()
    {
        return $this->belongsTo(Task::class, 'parent_task_id');
    }
}
