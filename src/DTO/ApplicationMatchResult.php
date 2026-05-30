<?php

namespace App\DTO;

class ApplicationMatchResult
{
    public function __construct(
        public readonly float $score,
        public readonly string $compatibilityLevel,
        public readonly bool $highlyCompatible
    ) {}
}