<?php
namespace App\Enum;

final class TaskStatus
{
    const CREATED = 1;
    const COMPLETED = 2;
    const NOT_COMPLETED = 3;
    const CANCELLED = 4;

    public static function getAvailableStatuses()
    {
        return [
            self::CREATED,
            self::COMPLETED,
            self::NOT_COMPLETED,
            self::CANCELLED
        ];
    }
}