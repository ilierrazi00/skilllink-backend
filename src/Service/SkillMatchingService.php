<?php

namespace App\Service;

use App\Entity\CandidateProfile;
use App\Entity\JobOffer;
use App\DTO\ApplicationMatchResult;

class SkillMatchingService
{
    public function calculateMatchResult(
        CandidateProfile $candidate,
        JobOffer $jobOffer
    ): ApplicationMatchResult {

        $candidateSkills = $candidate->getSkills()->toArray();
        $jobSkills = $jobOffer->getSkills()->toArray();

        if (count($jobSkills) === 0) {
            return new ApplicationMatchResult(
                0,
                'Aucune compétence requise',
                false
            );
        }

        $matchingSkills = array_filter(
            $candidateSkills,
            fn($candidateSkill) =>
                in_array($candidateSkill, $jobSkills, true)
        );

        $score = round(
            (count($matchingSkills) / count($jobSkills)) * 100,
            2
        );

        $compatibilityLevel = match (true) {
            $score >= 80 => 'Excellent',
            $score >= 60 => 'Bon',
            $score >= 40 => 'Moyen',
            default => 'Faible',
        };

        return new ApplicationMatchResult(
            $score,
            $compatibilityLevel,
            $score >= 70
        );
    }

    public function calculateMatchScore(
        CandidateProfile $candidate,
        JobOffer $jobOffer
    ): float {
        return $this->calculateMatchResult(
            $candidate,
            $jobOffer
        )->score;
    }

    public function isHighlyCompatible(
        CandidateProfile $candidate,
        JobOffer $jobOffer,
        float $threshold = 70
    ): bool {
        return $this->calculateMatchScore(
            $candidate,
            $jobOffer
        ) >= $threshold;
    }
}