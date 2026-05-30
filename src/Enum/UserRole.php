<?php

namespace App\Enum;

enum UserRole: string
{
    case CANDIDATE = 'candidate';
    case RECRUITER = 'recruiter';
    case ADMIN = 'admin';

    public function securityRole(): string
    {
        return match($this) {
            self::CANDIDATE => 'ROLE_CANDIDATE',
            self::RECRUITER => 'ROLE_RECRUITER',
            self::ADMIN => 'ROLE_ADMIN',
        };
    }
}