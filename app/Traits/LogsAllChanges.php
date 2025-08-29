<?php

namespace App\Traits;

use Spatie\Activitylog\LogOptions;

trait LogsAllChanges
{
    public function getActivitylogOptions(): LogOptions
    {
        $name = $this->getTable();

        return LogOptions::defaults()
            ->useLogName($name)
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $e) => "{$name} {$e}");
    }

    protected static $recordEvents = ['created','updated','deleted','restored'];
}
