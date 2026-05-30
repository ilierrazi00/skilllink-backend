<?php

namespace App\Controller;

use App\Repository\ApplicationRepository;
use App\Repository\CandidateProfileRepository;
use App\Repository\CompanyRepository;
use App\Repository\JobOfferRepository;
use App\Service\SkillMatchingService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(
        CandidateProfileRepository $candidateProfileRepository,
        CompanyRepository $companyRepository,
        JobOfferRepository $jobOfferRepository,
        ApplicationRepository $applicationRepository,
        SkillMatchingService $skillMatchingService
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $totalCandidates = 0;
        $totalCompanies = 0;
        $totalJobOffers = 0;
        $totalApplications = 0;

        $acceptedApplications = 0;
        $rejectedApplications = 0;
        $interviewApplications = 0;

        $averageMatchScore = 0;
        $recentApplications = [];
        $applications = [];

        if ($this->isGranted('ROLE_ADMIN')) {
            $totalCandidates = $candidateProfileRepository->count([]);
            $totalCompanies = $companyRepository->count([]);
            $totalJobOffers = $jobOfferRepository->count([]);
            $totalApplications = $applicationRepository->count([]);
            $applications = $applicationRepository->findAll();
        } elseif ($this->isGranted('ROLE_RECRUITER')) {
            $totalCompanies = $companyRepository->count([]);
            $totalJobOffers = $jobOfferRepository->count([]);
            $totalApplications = $applicationRepository->count([]);
            $applications = $applicationRepository->findAll();
        } else {
            $totalCandidates = $candidateProfileRepository->count([]);
            $totalJobOffers = $jobOfferRepository->count([]);
            $applications = $applicationRepository->findAll();
            $totalApplications = count($applications);
        }

        $totalMatchScore = 0;
        $matchCount = 0;

        foreach ($applications as $application) {
            switch ($application->getStatut()) {
                case 'Acceptée':
                    $acceptedApplications++;
                    break;

                case 'Refusée':
                    $rejectedApplications++;
                    break;

                case 'Entretien':
                    $interviewApplications++;
                    break;
            }

            $candidateProfile = $application->getCandidateProfile();
            $jobOffer = $application->getJobOffer();

            if ($candidateProfile !== null && $jobOffer !== null) {
                $score = $skillMatchingService->calculateMatchScore(
                    $candidateProfile,
                    $jobOffer
                );

                $totalMatchScore += $score;
                $matchCount++;
            }
        }

        $averageMatchScore = $matchCount > 0
            ? round($totalMatchScore / $matchCount, 2)
            : 0;

        $recentApplications = array_slice(
            $applicationRepository->findBy(
                [],
                ['dateCandidature' => 'DESC']
            ),
            0,
            5
        );

        return $this->render('dashboard/index.html.twig', [
            'totalCandidates' => $totalCandidates,
            'totalCompanies' => $totalCompanies,
            'totalJobOffers' => $totalJobOffers,
            'totalApplications' => $totalApplications,
            'acceptedApplications' => $acceptedApplications,
            'rejectedApplications' => $rejectedApplications,
            'interviewApplications' => $interviewApplications,
            'averageMatchScore' => $averageMatchScore,
            'recentApplications' => $recentApplications,
            'isAdmin' => $this->isGranted('ROLE_ADMIN'),
            'isRecruiter' => $this->isGranted('ROLE_RECRUITER'),
            'isCandidate' => $this->isGranted('ROLE_CANDIDATE'),
        ]);
    }
}