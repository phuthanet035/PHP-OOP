<?php

namespace App\Enums;

/**
 * User Role Backed Enum
 */
enum UserRole: string
{
    case User       = 'user';
    case Technician = 'technician';
    case Admin      = 'admin';

    public function label(): string
    {
        return match ($this) {
            self::User       => 'ผู้แจ้งซ่อม (General User)',
            self::Technician => 'ช่างเทคนิค (Technician)',
            self::Admin      => 'ผู้ดูแลระบบ (Administrator)',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::User       => 'bg-emerald-100 text-emerald-800 border-emerald-300 dark:bg-emerald-900/30 dark:text-emerald-300',
            self::Technician => 'bg-amber-100 text-amber-800 border-amber-300 dark:bg-amber-900/30 dark:text-amber-300',
            self::Admin      => 'bg-purple-100 text-purple-800 border-purple-300 dark:bg-purple-900/30 dark:text-purple-300',
        };
    }
}
