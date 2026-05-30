<?php

namespace App\Controller\Api;

use App\Entity\Application;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use App\Entity\User;

class UpdateApplicationStatusController
    extends AbstractController
{
    #[Route(
        '/api/applications/{id}/status',
        name: 'api_update_application_status',
        methods: ['PUT']
    )]
    public function updateStatus(

        Application $application,

        Request $request,

        EntityManagerInterface $entityManager,

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
                'message' => 'Access denied'
            ], 403);
        }

        /*
         |--------------------------------------------------------------------------
         | REQUEST DATA
         |--------------------------------------------------------------------------
         */
        $data = json_decode(
            $request->getContent(),
            true
        );

        $status =
            $data['status'] ?? null;

        /*
         |--------------------------------------------------------------------------
         | VALIDATION
         |--------------------------------------------------------------------------
         */
        $allowedStatuses = [

            'En attente',
            'Acceptée',
            'Refusée',
        ];

        if (

            !$status ||

            !in_array(
                $status,
                $allowedStatuses
            )

        ) {

            return $this->json([

                'message' =>
                    'Statut invalide'
            ], 400);
        }

        /*
         |--------------------------------------------------------------------------
         | UPDATE STATUS
         |--------------------------------------------------------------------------
         */
        $application->setStatut(
            $status
        );

        $entityManager->persist(
            $application
        );

        $entityManager->flush();

        /*
         |--------------------------------------------------------------------------
         | RESPONSE
         |--------------------------------------------------------------------------
         */
        return $this->json([

            'message' =>
                'Statut mis à jour avec succès',

            'status' =>
                $application->getStatut(),
        ]);
    }
}