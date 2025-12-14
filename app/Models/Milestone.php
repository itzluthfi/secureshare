<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Milestone extends Model
{
    use HasFactory;

    protected $table = 'project_milestones';

    protected $fillable = [
        'project_id',
        'title',
        'description',
        'type',
        'start_date',
        'scheduled_date',
        'scheduled_time',
        'is_completed',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'scheduled_date' => 'date',
        'is_completed' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopeCompleted($query)
    {
        return $query->where('is_completed', true);
    }

    public function scopePending($query)
    {
        return $query->where('is_completed', false);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('scheduled_date', '>=', now())
            ->where('is_completed', false)
            ->orderBy('scheduled_date', 'asc');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    // Helper methods
    
    /**
     * Get all dates between start_date and scheduled_date
     * Returns array of date strings in Y-m-d format
     */
    public function getDateRange()
    {
        // If no scheduled_date, return empty array
        if (!$this->scheduled_date) {
            return [];
        }

        // If no start_date, just return the scheduled_date
        if (!$this->start_date) {
            return [$this->scheduled_date->format('Y-m-d')];
        }

        $dates = [];
        $start = Carbon::parse($this->start_date);
        $end = Carbon::parse($this->scheduled_date);

        // Generate all dates from start to end
        while ($start->lte($end)) {
            $dates[] = $start->format('Y-m-d');
            $start->addDay();
        }

        return $dates;
    }

    /**
     * Check if milestone is active on a specific date
     */
    public function isActiveOnDate($date)
    {
        $checkDate = Carbon::parse($date);
        
        if (!$this->scheduled_date) {
            return false;
        }

        // If no start_date, check if it's the scheduled_date
        if (!$this->start_date) {
            return $checkDate->isSameDay($this->scheduled_date);
        }

        // Check if date is between start_date and scheduled_date
        return $checkDate->between($this->start_date, $this->scheduled_date, true);
    }

    /**
     * Get milestone duration in days
     */
    public function getDurationDays()
    {
        if (!$this->start_date || !$this->scheduled_date) {
            return 0;
        }

        return $this->start_date->diffInDays($this->scheduled_date) + 1;
    }

    /**
     * Check if milestone is overdue
     */
    public function isOverdue()
    {
        return $this->scheduled_date 
            && $this->scheduled_date->isPast() 
            && !$this->is_completed;
    }

    /**
     * Check if milestone is today
     */
    public function isToday()
    {
        return $this->scheduled_date && $this->scheduled_date->isToday();
    }

    /**
     * Get days until milestone
     */
    public function daysUntil()
    {
        if (!$this->scheduled_date) {
            return null;
        }

        return now()->diffInDays($this->scheduled_date, false);
    }

    /**
     * Get type color
     */
    public function getTypeColor()
    {
        return match($this->type) {
            'deadline' => '#EF4444',
            'meeting' => '#8B5CF6',
            'review' => '#F59E0B',
            'launch' => '#10B981',
            'milestone' => '#4F7FFF',
            default => '#6B7280',
        };
    }

    /**
     * Get formatted date with time if available
     */
    public function getFormattedDateTime()
    {
        $date = $this->scheduled_date->format('M d, Y');
        
        if ($this->scheduled_time) {
            return $date . ' at ' . $this->scheduled_time;
        }
        
        return $date;
    }

    /**
     * Get status badge HTML
     */
    public function getStatusBadge()
    {
        if ($this->is_completed) {
            return '<span class="badge badge-success">Completed</span>';
        }

        if ($this->isOverdue()) {
            return '<span class="badge badge-danger">Overdue</span>';
        }

        if ($this->isToday()) {
            return '<span class="badge badge-warning">Today</span>';
        }

        return '<span class="badge badge-info">Upcoming</span>';
    }
}