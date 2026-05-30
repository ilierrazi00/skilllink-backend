<?php

namespace App\Controller\Api;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class UpdateProfileController extends AbstractController
{
    #[Route('/api/users/me', name: 'api_update_profile', methods: ['PUT'])]
    public function updateProfile(
        Request $request,
        EntityManagerInterface $entityManager,
        #[CurrentUser] ?User $user
    ): JsonResponse {

        if (!$user) {

            return $this->json([
                'message' => 'Unauthorized'
            ], 401);
        }

        $data = json_decode(
            $request->getContent(),
            true
        );

        if (isset($data['nom'])) {
            $user->setNom($data['nom']);
        }

        if (isset($data['prenom'])) {
            $user->setPrenom($data['prenom']);
        }

        $entityManager->flush();

        return $this->json([
            'message' => 'Profil mis à jour avec succès',
            'user' => [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'nom' => $user->getNom(),
                'prenom' => $user->getPrenom(),
                
            ]
        ]);
    }
}