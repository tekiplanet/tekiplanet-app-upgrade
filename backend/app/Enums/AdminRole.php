<?php

namespace App\Enums;

enum AdminRole: string
{
    case SUPER_ADMIN = 'super_admin';
    case ADMIN = 'admin';
    case MANAGEMENT = 'management';
    case TUTOR = 'tutor';
    case SALES = 'sales';
    case FINANCE = 'finance';

    public static function getValues(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function getLabels(): array
    {
        return [
            self::SUPER_ADMIN->value => 'Super Admin',
            self::ADMIN->value => 'Admin',
            self::MANAGEMENT->value => 'Management',
            self::TUTOR->value => 'Tutor',
            self::SALES->value => 'Sales',
            self::FINANCE->value => 'Finance',
        ];
    }
} 