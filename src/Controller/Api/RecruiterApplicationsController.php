<?php

namespace App\Controller\Api;

use App\Entity\User;
use App\Repository\ApplicationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class RecruiterApplicationsController
    extends AbstractController
{
    #[Route(
        '/api/recruiter/applications',
        name: 'api_recruiter_applications',
        methods: ['GET']
    )]
    public function index(

        ApplicationRepository $applicationRepository,

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

                'message' =>
                    'Unauthorized'
            ], 401);
        }

        /*
         |--------------------------------------------------------------------------
         | ROLE CHECK
         |--------------------------------------------------------------------------
         */
        if (

            !in_array(
                'ROLE_RECRUITER',
                $user->getRoles()
            )

            &&

            !in_array(
                'ROLE_ADMIN',
                $user->getRoles()
            )

        ) {

            return $this->json([

                'message' =>
                    'Access denied'
            ], 403);
        }

        /*
         |--------------------------------------------------------------------------
         | GET APPLICATIONS
         |--------------------------------------------------------------------------
         */
        $applications =
            $applicationRepository->findAll();

        $data = [];

        foreach ($applications as $application) {

            $candidate =
                $application->getCandidateProfile();

            $jobOffer =
                $application->getJobOffer();

            $data[] = [

                'id' =>
                    $application->getId(),

                'status' =>
                    $application->getStatut(),

                'date' =>

                    $application
                        ->getDateCandidature()
                        ?->format('d/m/Y H:i'),

                /*
                 |--------------------------------------------------------------------------
                 | CANDIDATE
                 |--------------------------------------------------------------------------
                 */
                'candidate' => [

                    'id' =>
                        $candidate?->getId(),

                    'skills' =>
                        $candidate?->getSkills(),

                    'experience' =>
                        $candidate?->getExperience(),

                    'education' =>
                        $candidate?->getEducation(),

                    'user' => [

                        'id' =>
                            $candidate?->getUser()?->getId(),

                        'nom' =>
                            $candidate?->getUser()?->getNom(),

                        'prenom' =>
                            $candidate?->getUser()?->getPrenom(),

                        'email' =>
                            $candidate?->getUser()?->getEmail(),
                    ],
                ],

                /*
                 |--------------------------------------------------------------------------
                 | JOB OFFER
                 |--------------------------------------------------------------------------
                 */
                'jobOffer' => [

                    'id' =>
                        $jobOffer?->getId(),

                    'title' =>
                        $jobOffer?->getTitle(),

                    'company' =>
                        $jobOffer?->getCompany(),
                ],
            ];
        }

        /*
         |--------------------------------------------------------------------------
         | RESPONSE
         |--------------------------------------------------------------------------
         */
        return $this->json($data);
    }
}