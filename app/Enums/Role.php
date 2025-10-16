<?php

namespace App\Enums;

enum Role: string
{
    case ADMIN = 'admin';
    case MARKETER = 'marketer';
    case DELIVERY_AGENT = 'delivery_agent';
    case CALL_AGENT = 'call_agent';
    case MANAGER = 'manager';
}