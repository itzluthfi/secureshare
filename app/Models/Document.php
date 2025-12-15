<?php

namespace App\Models;

use App\Traits\HasEncryptedIds;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Document extends Model
{
    use HasFactory, SoftDeletes, HasEncryptedIds;

    protected $fillable = [
        'project_id',
        'name',
        'original_name',
        'file_path',
        'file_type',
        'file_size',
        'encryption_key',
        'encryption_iv',
        'current_version',
        'uploaded_by',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'current_version' => 'integer',
    ];

    protected $hidden = [
        'encryption_key',
        'encryption_iv',
    ];

    protected $appends = ['encrypted_id'];

    // Relationships
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function versions()
    {
        return $this->hasMany(DocumentVersion::class)->orderBy('version_number', 'desc');
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable')->whereNull('parent_id')->orderBy('created_at', 'desc');
    }

    public function allComments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    // Helper methods
    public function getFileSizeHumanAttribute()
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
