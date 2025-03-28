<?php

namespace App\Enums;

enum PaymentStatus: string
{
    const PENDING = 'pending';
    const SUCCESSFUL = 'successful';
    const FAILED = 'failed';

    public static function listStatus(): array
    {
        return [
            self::PENDING => "Pending",
            self::SUCCESSFUL => "Successful",
            self::FAILED => "Failed",
        ];
    }

    public static function getLabel($status): string
    {
        return self::listStatus()[$status] ?? 'Unknown';
    }
}
