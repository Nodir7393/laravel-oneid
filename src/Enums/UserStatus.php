<?php

namespace Nodir\OneId\Enums;

enum UserStatus: string
{
    case PENDING = 'pending';
    case ACTIVE  = 'active';
    case BLOCKED = 'blocked';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Kutilmoqda',
            self::ACTIVE  => 'Faol',
            self::BLOCKED => 'Bloklangan',
        };
    }
}
