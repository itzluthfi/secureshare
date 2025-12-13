<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditLogService
{
    /**
     * Log an activity
     *
     * @param string $action
     * @param string $description
     * @param string|null $modelType
     * @param int|null $modelId
     * @param array|null $oldValues
     * @param array|null $newValues
     * @return AuditLog
     */
    public function log(
        string $action,
        string $description,
        $modelType = null,
        $modelId = null,
        $oldValues = null,
        $newValues = null
    ) {
        return AuditLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'model_type' => $modelType,
            'model_id' => $modelId,
            'description' => $description,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }

    /**
     * Log login activity
     */
    public function logLogin($user)
    {
        return $this->log(
            'login',
            "User {$user->name} logged in",
            'App\Models\User',
            $user->id
        );
    }

    /**
     * Log logout activity
     */
    public function logLogout($user)
    {
        return $this->log(
            'logout',
            "User {$user->name} logged out",
            'App\Models\User',
            $user->id
        );
    }

    /**
     * Log document upload
     */
    public function logDocumentUpload($document)
    {
        return $this->log(
            'upload',
            "Document '{$document->name}' uploaded to project '{$document->project->name}'",
            'App\Models\Document',
            $document->id,
            null,
            $document->toArray()
        );
    }

    /**
     * Log document download
     */
    public function logDocumentDownload($document)
    {
        return $this->log(
            'download',
            "Document '{$document->name}' downloaded",
            'App\Models\Document',
            $document->id
        );
    }

    /**
     * Log model creation
     */
    public function logCreate($model, $description = null)
    {
        $modelName = class_basename($model);
        $desc = $description ?? "{$modelName} created";
        
        return $this->log(
            'create',
            $desc,
            get_class($model),
            $model->id,
            null,
            $model->toArray()
        );
    }

    /**
     * Log model update
     */
    public function logUpdate($model, $oldValues, $description = null)
    {
        $modelName = class_basename($model);
        $desc = $description ?? "{$modelName} updated";
        
        return $this->log(
            'update',
            $desc,
            get_class($model),
            $model->id,
            $oldValues,
            $model->toArray()
        );
    }

    /**
     * Log model deletion
     */
    public function logDelete($model, $description = null)
    {
        $modelName = class_basename($model);
        $desc = $description ?? "{$modelName} deleted";
        
        return $this->log(
            'delete',
            $desc,
            get_class($model),
            $model->id,
            $model->toArray(),
            null
        );
    }
}
