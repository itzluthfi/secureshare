<?php

namespace App\Models;

use App\Traits\HasEncryptedIds;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory, SoftDeletes, HasEncryptedIds;

    protected $fillable = [
        'name',
        'description',
        'created_by',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $appends = ['encrypted_id'];

    // Relationships
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'project_members')
            ->withPivot('role', 'status', 'responded_at')
            ->withTimestamps();
    }
    
    public function pendingInvitations()
    {
        return $this->belongsToMany(User::class, 'project_members')
            ->withPivot('role', 'status', 'responded_at')
            ->wherePivot('status', 'pending')
            ->withTimestamps();
    }

    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable')->orderBy('created_at', 'desc');
    }

    // Helper methods
    public function addMember($userId, $role = 'member')
    {
        return $this->members()->attach($userId, ['role' => $role]);
    }

    public function removeMember($userId)
    {
        return $this->members()->detach($userId);
    }

    public function updateMemberRole($userId, $role)
    {
        return $this->members()->updateExistingPivot($userId, ['role' => $role]);
    }

    public function hasMember($userId)
    {
        return $this->members()->where('user_id', $userId)->exists();
    }
}
