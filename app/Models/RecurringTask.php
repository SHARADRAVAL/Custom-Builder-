<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class RecurringTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'repeat_type',
        'start_date',
        'end_date',
        'daily_time',
        'weekly_time',
        'week_days',
        'monthly_date',
        'monthly_time',
        'monthly_day'
    ];

    protected $casts = [
        'week_days'    => 'array',
        'start_date'   => 'date',
        'end_date'     => 'date',
        'monthly_date' => 'date',
    ];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    /* -------------------------
       Helper methods
       ------------------------- */

    public function hasStarted(): bool
    {
        if (!$this->start_date) {
            return false;
        }
        return now()->startOfDay()->gte($this->start_date);
    }

    public function hasEnded(): bool
    {
        // If end_date is set, and TODAY is greater than end_date, it has ended.
        return $this->end_date && now()->startOfDay()->gt($this->end_date);
    }


    public function isActive(): bool
    {
        $now = now()->startOfDay(); //

        // 1. Check if the current date is at or after the start date
        $hasStarted = $this->start_date && $now->gte(Carbon::parse($this->start_date)->startOfDay()); //

        // 2. Check if there is an end date, and if we are still before or on it
        $hasNotEnded = true;
        if ($this->end_date) {
            $hasNotEnded = $now->lte(\Carbon\Carbon::parse($this->end_date)->startOfDay()); //
        }

        return $hasStarted && $hasNotEnded;
    }
}
