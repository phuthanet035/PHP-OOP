<?php

namespace App\Enums;

/**
 * Ticket Status Backed Enum (PHP 8.1+)
 * Matching Class Diagram 5 & State Machine 3
 */
enum TicketStatus: string
{
    case Open       = 'Open';
    case Assigned   = 'Assigned';
    case InProgress = 'InProgress';
    case Resolved   = 'Resolved';
    case Closed     = 'Closed';

    public function label(): string
    {
        return match ($this) {
            self::Open       => 'รอดำเนินการ (Open)',
            self::Assigned   => 'มอบหมายช่างแล้ว (Assigned)',
            self::InProgress => 'กำลังดำเนินการซ่อม (In Progress)',
            self::Resolved   => 'ซ่อมแซมเสร็จสิ้น (Resolved)',
            self::Closed     => 'ปิดงานสมบูรณ์ (Closed)',
        };
    }

    public function shortLabel(): string
    {
        return match ($this) {
            self::Open       => 'รอดำเนินการ',
            self::Assigned   => 'มอบหมายแล้ว',
            self::InProgress => 'กำลังซ่อม',
            self::Resolved   => 'ซ่อมเสร็จแล้ว',
            self::Closed     => 'ปิดงานแล้ว',
        };
    }

    public function stepIndex(): int
    {
        return match ($this) {
            self::Open       => 1,
            self::Assigned   => 2,
            self::InProgress => 3,
            self::Resolved   => 4,
            self::Closed     => 5,
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Open       => 'bg-amber-100 text-amber-800 border-amber-300 dark:bg-amber-900/30 dark:text-amber-300',
            self::Assigned   => 'bg-blue-100 text-blue-800 border-blue-300 dark:bg-blue-900/30 dark:text-blue-300',
            self::InProgress => 'bg-indigo-100 text-indigo-800 border-indigo-300 dark:bg-indigo-900/30 dark:text-indigo-300',
            self::Resolved   => 'bg-emerald-100 text-emerald-800 border-emerald-300 dark:bg-emerald-900/30 dark:text-emerald-300',
            self::Closed     => 'bg-slate-100 text-slate-800 border-slate-300 dark:bg-slate-800 dark:text-slate-300',
        };
    }

    public function dotColor(): string
    {
        return match ($this) {
            self::Open       => 'bg-amber-500',
            self::Assigned   => 'bg-blue-500',
            self::InProgress => 'bg-indigo-500',
            self::Resolved   => 'bg-emerald-500',
            self::Closed     => 'bg-slate-400',
        };
    }
}
