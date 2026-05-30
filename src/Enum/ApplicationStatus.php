<?php

namespace App\Enum;

enum ApplicationStatus: string
{
    case PENDING = 'En attente';
    case REVIEW = 'En cours';
    case INTERVIEW = 'Entretien';
    case ACCEPTED = 'Acceptée';
    case REJECTED = 'Refusée';
}