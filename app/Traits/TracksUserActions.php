<?php

namespace App\Traits;

use App\Models\ActivityLog;

trait TracksUserActions
{
    public static function bootTracksUserActions()
    {
        static::creating(function ($model) {
            if (auth()->check()) {
                $model->created_by = auth()->id();
                $model->updated_by = auth()->id();
            }
        });

        static::updating(function ($model) {
            if (auth()->check()) {
                $model->updated_by = auth()->id();
            }
        });

        static::created(function ($model) {
            ActivityLog::log(
                'create',
                self::getActionDescription('create', $model),
                $model,
                null,
                $model->getAttributes()
            );
        });

        static::updated(function ($model) {
            $dirty = $model->getDirty();
            if (!empty($dirty)) {
                ActivityLog::log(
                    'update',
                    self::getActionDescription('update', $model),
                    $model,
                    $model->getOriginal(),
                    $dirty
                );
            }
        });

        static::deleted(function ($model) {
            ActivityLog::log(
                'delete',
                self::getActionDescription('delete', $model),
                $model,
                $model->getAttributes(),
                null
            );
        });
    }

    protected static function getActionDescription(string $action, $model): string
    {
        $modelName = class_basename($model);
        $identifier = $model->nom ?? $model->name ?? $model->matricule ?? $model->id;
        
        return match($action) {
            'create' => "Création de {$modelName}: {$identifier}",
            'update' => "Modification de {$modelName}: {$identifier}",
            'delete' => "Suppression de {$modelName}: {$identifier}",
            default => "{$action} {$modelName}: {$identifier}",
        };
    }

    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }
}
