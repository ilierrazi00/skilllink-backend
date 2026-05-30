<?php

namespace App\Controller\Api;

use App\Entity\User;
use App\Repository\ApplicationRepository;
use App\Repository\CandidateProfileRepository;
use App\Repository\CompanyRepository;
use App\Repository\JobOfferRepository;
use App\Service\SkillMatchingService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class DashboardController extends AbstractController
{
    #[Route(
        '/api/dashboard',
        name: 'api_dashboard',
        methods: ['GET']
    )]
    public function index(

        CandidateProfileRepository $candidateProfileRepository,

        CompanyRepository $companyRepository,

        JobOfferRepository $jobOfferRepository,

        ApplicationRepository $applicationRepository,

        SkillMatchingService $skillMatchingService,

        #[CurrentUser]
        ?User $user

    ): JsonResponse {

        /*
         |--------------------------------------------------------------------------
         | SECURITY
         |--------------------------------------------------------------------------
         */
        if (!$user) {

            return $this->json([
                'message' => 'Unauthorized'
            ], 401);
        }

        $totalCandidates = 0;
        $totalCompanies = 0;
        $totalJobOffers = 0;
        $totalApplications = 0;

        $acceptedApplications = 0;
        $rejectedApplications = 0;
        $interviewApplications = 0;

        $averageMatchScore = 0;

        $applications = [];

        /*
         |--------------------------------------------------------------------------
         | ROLE LOGIC
         |--------------------------------------------------------------------------
         */
        if (in_array('ROLE_ADMIN', $user->getRoles())) {

            $totalCandidates =
                $candidateProfileRepository->count([]);

            $totalCompanies =
                $companyRepository->count([]);

            $totalJobOffers =
                $jobOfferRepository->count([]);

            $totalApplications =
                $applicationRepository->count([]);

            $applications =
                $applicationRepository->findAll();

        } elseif (
            in_array(
                'ROLE_RECRUITER',
                $user->getRoles()
            )
        ) {

            $totalCompanies =
                $companyRepository->count([]);

            $totalJobOffers =
                $jobOfferRepository->count([]);

            $totalApplications =
                $applicationRepository->count([]);

            $applications =
                $applicationRepository->findAll();

        } else {

            $totalCandidates =
                $candidateProfileRepository->count([]);

            $totalJobOffers =
                $jobOfferRepository->count([]);

            $applications =
                $applicationRepository->findAll();

            $totalApplications =
                count($applications);
        }

        /*
         |--------------------------------------------------------------------------
         | APPLICATION STATS
         |--------------------------------------------------------------------------
         */
        $totalMatchScore = 0;
        $matchCount = 0;

        foreach ($applications as $application) {

            switch (
                $application->getStatut()
            ) {

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

            $candidateProfile =
                $application->getCandidateProfile();

            $jobOffer =
                $application->getJobOffer();

            if (
                $candidateProfile !== null
                &&
                $jobOffer !== null
            ) {

                $score =
                    $skillMatchingService
                        ->calculateMatchScore(
                            $candidateProfile,
                            $jobOffer
                        );

                $totalMatchScore +=
                    $score;

                $matchCount++;
            }
        }

        $averageMatchScore =
            $matchCount > 0

                ? round(
                    $totalMatchScore
                    /
                    $matchCount,
                    2
                )

                : 0;

        /*
         |--------------------------------------------------------------------------
         | RESPONSE
         |--------------------------------------------------------------------------
         */
        return $this->json([

            'roleType' =>
                $user->getRoleType(),

            'prenom' =>
                $user->getPrenom(),

            'nom' =>
                $user->getNom(),

            'totalCandidates' =>
                $totalCandidates,

            'totalCompanies' =>
                $totalCompanies,

            'totalJobOffers' =>
                $totalJobOffers,

            'totalApplications' =>
                $totalApplications,

            'acceptedApplications' =>
                $acceptedApplications,

            'rejectedApplications' =>
                $rejectedApplications,

            'interviewApplications' =>
                $interviewApplications,

            'averageMatchScore' =>
                $averageMatchScore,

            'isAdmin' =>
                in_array(
                    'ROLE_ADMIN',
                    $user->getRoles()
                ),

            'isRecruiter' =>
                in_array(
                    'ROLE_RECRUITER',
                    $user->getRoles()
                ),

            'isCandidate' =>
                in_array(
                    'ROLE_CANDIDATE',
                    $user->getRoles()
                ),
        ]);
    }
}