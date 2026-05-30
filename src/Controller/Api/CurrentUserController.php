<?php

namespace App\Controller\Api;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class CurrentUserController
    extends AbstractController
{
    #[Route(
        '/api/me',
        name: 'api_current_user',
        methods: ['GET']
    )]
    public function me(

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
         | RESPONSE
         |--------------------------------------------------------------------------
         */
        return $this->json([

            'id' =>
                $user->getId(),

            'email' =>
                $user->getEmail(),

            'nom' =>
                $user->getNom(),

            'prenom' =>
                $user->getPrenom(),

            'roles' =>
                $user->getRoles(),
        ]);
    }
}