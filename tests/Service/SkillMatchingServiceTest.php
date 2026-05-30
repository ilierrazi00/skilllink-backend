<?php

namespace App\Tests\Service;

use App\Entity\CandidateProfile;
use App\Entity\JobOffer;
use App\Entity\Skill;
use App\Service\SkillMatchingService;
use PHPUnit\Framework\TestCase;

class SkillMatchingServiceTest extends TestCase
{
    public function testCalculateMatchResultExcellent(): void
    {
        $service = new SkillMatchingService();

        $php = new Skill();
        $php->setNom('PHP');

        $symfony = new Skill();
        $symfony->setNom('Symfony');

        $candidate = new CandidateProfile();
        $candidate->addSkill($php);
        $candidate->addSkill($symfony);

        $jobOffer = new JobOffer();
        $jobOffer->addSkill($php);
        $jobOffer->addSkill($symfony);

        $result = $service->calculateMatchResult(
            $candidate,
            $jobOffer
        );

        $this->assertEquals(100, $result->score);
        $this->assertEquals('Excellent', $result->compatibilityLevel);
        $this->assertTrue($result->highlyCompatible);
    }

    public function testCalculateMatchResultFaible(): void
    {
        $service = new SkillMatchingService();

        $php = new Skill();
        $php->setNom('PHP');

        $java = new Skill();
        $java->setNom('Java');

        $candidate = new CandidateProfile();
        $candidate->addSkill($php);

        $jobOffer = new JobOffer();
        $jobOffer->addSkill($java);

        $result = $service->calculateMatchResult(
            $candidate,
            $jobOffer
        );

        $this->assertEquals(0, $result->score);
        $this->assertEquals('Faible', $result->compatibilityLevel);
        $this->assertFalse($result->highlyCompatible);
    }

    public function testIsHighlyCompatible(): void
    {
        $service = new SkillMatchingService();

        $php = new Skill();
        $php->setNom('PHP');

        $candidate = new CandidateProfile();
        $candidate->addSkill($php);

        $jobOffer = new JobOffer();
        $jobOffer->addSkill($php);

        $this->assertTrue(
            $service->isHighlyCompatible(
                $candidate,
                $jobOffer
            )
        );
    }
}