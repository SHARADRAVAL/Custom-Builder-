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
        'comment',   // ✅ add this
        'feedback',  // ✅ add this
    ];


    protected $casts = [
        'start_time'    => 'datetime',
        'end_time'      => 'datetime',
        'started_at'    => 'datetime',
        'completed_at'  => 'datetime',
        'reminder_sent' => 'boolean',
    ];

    /**
     * Relationship: Recurring Rule
     */
    public function recurring()
    {
        return $this->hasOne(RecurringTask::class);
    }

    /**
     * Relationship: Task Notes
     */
    public function notes()
    {
        return $this->hasMany(Note::class);
    }

    /**
     * Relationship: Single User (Legacy Support)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship: Multiple Users (Many-to-Many)
     * Requirement: Needs 'task_user' pivot table
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'task_user');
    }
}
