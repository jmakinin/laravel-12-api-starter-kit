<?php

namespace App\Enums;

enum Roles: string
{
    case SUPER_ADMIN = "super_admin";
    case ADMIN = "admin";
    case MANAGER = "manager";
    case VIEWER = "viewer";
}
