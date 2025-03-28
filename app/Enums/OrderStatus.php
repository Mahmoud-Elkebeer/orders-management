<?php

namespace App\Enums;

enum OrderStatus: string
{
    const PENDING = 'pending';
    const CONFIRMED = 'confirmed';
    const CANCELLED = 'cancelled';
    const PAID = 'paid';

    public static function listStatus(): array
    {
        return [
            self::PENDING => "Pending",
            self::CONFIRMED => "Confirmed",
            self::CANCELLED => "Cancelled",
            self::PAID => "Paid",
        ];
    }

    public static function getLabel($status): string
    {
        return self::listStatus()[$status] ?? 'Unknown';
    }
}
