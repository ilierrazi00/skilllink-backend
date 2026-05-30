<?php

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use App\Entity\User;

class MeController extends AbstractController
{
    #[Route('/api/me', name: 'api_me', methods: ['GET'])]
    public function me(
        #[CurrentUser] ?User $user
    ): JsonResponse {

        if (!$user) {
            return $this->json([
                'message' => 'Unauthorized'
            ], 401);
        }

        return $this->json([
    'id' => $user->getId(),
    'email' => $user->getEmail(),
    'roles' => $user->getRoles(),
    'roleType' => $user->getRoleType(),

    'nom' => $user->getNom(),
    'prenom' => $user->getPrenom(),
]);
    }
}