<?php

namespace App\Enums;

enum UserRole: string
{
    case MANAGER = 'manager';
    case SUPER_MANAGER = 'super_manager';
    case ADMIN = 'admin';
}
