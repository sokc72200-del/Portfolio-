<?php

namespace App\Enums;

enum SkillCategory: string
{
    case Frontend = 'frontend';
    case Backend = 'backend';
    case Database = 'database';
    case DevOps = 'devops';
    case Other = 'other';
}
