<?php

namespace App\Enums;

enum TaskStatus: string
{
    case PENDING = 'pending';
    case IN_PROGRESS = 'in_progress';
    case COMPLETED = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'در انتظار',
            self::IN_PROGRESS => 'در حال انجام',
            self::COMPLETED => 'تکمیل‌شده',
        };
    }
}
