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
        'next_run_at',
        'end_date',
        'daily_time',
        'weekly_time',
        'week_days',
        'monthly_date',
        'monthly_time',
        'monthly_day',
        'skip_dates', 
    ];

    protected $casts = [
        'next_run_at' => 'datetime',
        'week_days'   => 'array',
        'skip_dates'  => 'array', 
        'start_date'  => 'date',
        'end_date'    => 'date',
        'monthly_date' => 'date',
    ];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    // chack is Active Or not Today 
    public function isActive(): bool
    {
        $now = now()->startOfDay();
        $hasStarted = $this->start_date && $now->gte(Carbon::parse($this->start_date)->startOfDay());
        $hasNotEnded = !$this->end_date || $now->lte(Carbon::parse($this->end_date)->startOfDay());
        return $hasStarted && $hasNotEnded;
    }

    
     // Calculate the next run datetime for the recurring task.
    protected static function booted()
    {
        static::creating(function ($recurring) {
            if (!$recurring->next_run_at) {
                $recurring->next_run_at = $recurring->calculateNextRun();
            }
        });

        static::updating(function ($recurring) {
            if (!$recurring->next_run_at) {
                $recurring->next_run_at = $recurring->calculateNextRun();
            }
        });
    }

    // Calculate the next Run
    public function calculateNextRun(): ?Carbon
    {
        $current = $this->next_run_at ?? Carbon::parse($this->start_date);
        $now = now();

        switch ($this->repeat_type) {

            case 'daily':
                $next = $current->copy()->setTimeFrom($this->daily_time)->gte($now)
                    ? $current->copy()->setTimeFrom($this->daily_time)
                    : $current->copy()->addDay()->setTimeFrom($this->daily_time);
                break;

            case 'weekly':
                $weekDays = $this->week_days ?? [];
                $today = $now->dayName;
                $nextDay = collect($weekDays)
                    ->map(fn($d) => Carbon::parse("next {$d}"))
                    ->sort()
                    ->first();
                $next = $nextDay ? $nextDay->setTimeFrom($this->weekly_time) : null;
                break;

            case 'monthly':
                $day = $this->monthly_day ?? $current->day;
                $next = $current->copy()->day($day)->setTimeFrom($this->monthly_time);
                if ($next->lt($now)) $next->addMonth();
                break;
            
            // case 'yearly':
            //     $next = $current->copy()->setTimeFrom($this->yearly_time);
            //     $yearDay = $this->yearly_day ?? $current->day;                  // add year in feture 
            //     $next->day($yearDay);
            //     if ($next->lt($now)) $next->addYear();
            //     break;

            default:
                return null;
        }

        // Stop if past end_date
        if ($this->end_date && $next->gt(Carbon::parse($this->end_date))) {
            return null;
        }

        return $next;
    }
}
