<?php

namespace App\Enums;

enum TaskPriority: string
{
    case NONE = 'none';
    case LOW = 'low';
    case MEDIUM = 'medium';
    case HIGH = 'high';
    case URGENT = 'urgent';

    public function weight(): int
    {
        return match ($this) {
            self::URGENT => 40,
            self::HIGH => 30,
            self::MEDIUM => 20,
            self::LOW => 10,
            self::NONE => 0,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::URGENT => 'فوری',
            self::HIGH => 'بالا',
            self::MEDIUM => 'متوسط',
            self::LOW => 'پایین',
            self::NONE => 'بدون اولویت',
        };
    }
}
