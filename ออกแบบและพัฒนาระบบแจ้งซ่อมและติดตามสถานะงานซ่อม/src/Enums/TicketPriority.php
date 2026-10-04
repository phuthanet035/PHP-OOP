<?php

namespace App\Enums;

/**
 * Ticket Priority Backed Enum
 */
enum TicketPriority: string
{
    case Low    = 'Low';
    case Medium = 'Medium';
    case High   = 'High';
    case Urgent = 'Urgent';

    public function label(): string
    {
        return match ($this) {
            self::Low    => 'ต่ำ (Low)',
            self::Medium => 'ปานกลาง (Medium)',
            self::High   => 'สูง (High)',
            self::Urgent => 'เร่งด่วนที่สุด (Urgent)',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Low    => 'bg-slate-100 text-slate-700 border-slate-300 dark:bg-slate-800 dark:text-slate-300',
            self::Medium => 'bg-sky-100 text-sky-800 border-sky-300 dark:bg-sky-900/30 dark:text-sky-300',
            self::High   => 'bg-amber-100 text-amber-800 border-amber-300 dark:bg-amber-900/30 dark:text-amber-300',
            self::Urgent => 'bg-rose-100 text-rose-800 border-rose-300 dark:bg-rose-900/30 dark:text-rose-300 animate-pulse',
        };
    }
}
