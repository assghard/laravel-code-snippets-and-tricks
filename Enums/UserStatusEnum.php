<?php

declare(strict_types=1);

namespace App\Enums;

use Assghard\Laravel2fa\Traits\BaseEnumTrait;

enum UserStatusEnum: int
{
    use BaseEnumTrait;

    case Inactive = 1; // default status
    case Active = 2;
    case Suspended = 3;
    case Banned = 4;
    case Deleted = 5;

    public function label(): string
    {
        return __('user.statuses.'.$this->name);
    }

    public function isActive(): bool
    {
        return $this === self::Active;
    }

}
