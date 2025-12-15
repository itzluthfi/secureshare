<?php

namespace App\Models;

use App\Traits\HasEncryptedIds;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Task extends Model
{
    use HasFactory, SoftDeletes, HasEncryptedIds;

    const STATUS_TODO = 'todo';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_DONE = 'done';

    const PRIORITY_LOW = 'low';
    const PRIORITY_MEDIUM = 'medium';
    const PRIORITY_HIGH = 'high';

    protected $fillable = [
        'project_id',
        'title',
        'description',
        'status',
        'priority',
        'created_by',
        'start_date',
        'deadline',
    ];
    
    protected $appends = ['encrypted_id'];

    protected $casts = [
        'start_date' => 'date',
        'deadline' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relationships
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function assignees()
    {
        return $this->belongsToMany(User::class, 'task_assignees')
            ->withTimestamps()
            ->withPivot('assigned_at');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopeTodo($query)
    {
        return $query->where('status', self::STATUS_TODO);
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', self::STATUS_IN_PROGRESS);
    }

    public function scopeDone($query)
    {
        return $query->where('status', self::STATUS_DONE);
    }

    public function scopeOverdue($query)
    {
        return $query->where('deadline', '<', now())
            ->where('status', '!=', self::STATUS_DONE);
    }

    // Helper methods
    public function isOverdue()
    {
        return $this->deadline && $this->deadline->isPast() && $this->status !== self::STATUS_DONE;
    }

    /**
     * Get all dates between start_date and deadline
     * Returns array of date strings in Y-m-d format
     */
    public function getDateRange()
    {
        // If no deadline, return empty array
        if (!$this->deadline) {
            return [];
        }

        // If no start_date, just return the deadline
        if (!$this->start_date) {
            return [$this->deadline->format('Y-m-d')];
        }

        $dates = [];
        $start = Carbon::parse($this->start_date);
        $end = Carbon::parse($this->deadline);

        // Generate all dates from start to end
        while ($start->lte($end)) {
            $dates[] = $start->format('Y-m-d');
            $start->addDay();
        }

        return $dates;
    }

    /**
     * Check if task is active on a specific date
     */
    public function isActiveOnDate($date)
    {
        $checkDate = Carbon::parse($date);
        
        if (!$this->deadline) {
            return false;
        }

        // If no start_date, check if it's the deadline
        if (!$this->start_date) {
            return $checkDate->isSameDay($this->deadline);
        }

        // Check if date is between start_date and deadline
        return $checkDate->between($this->start_date, $this->deadline, true);
    }

    /**
     * Get task duration in days
     */
    public function getDurationDays()
    {
        if (!$this->start_date || !$this->deadline) {
            return 0;
        }

        return $this->start_date->diffInDays($this->deadline) + 1;
    }

    /**
     * Get status color
     */
    public function getStatusColor()
    {
        return match($this->status) {
            self::STATUS_TODO => '#6B7280',
            self::STATUS_IN_PROGRESS => '#F59E0B',
            self::STATUS_DONE => '#10B981',
            default => '#6B7280',
        };
    }

    /**
     * Get priority color
     */
    public function getPriorityColor()
    {
        return match($this->priority) {
            self::PRIORITY_LOW => '#10B981',
            self::PRIORITY_MEDIUM => '#F59E0B',
            self::PRIORITY_HIGH => '#EF4444',
            default => '#6B7280',
        };
    }
}