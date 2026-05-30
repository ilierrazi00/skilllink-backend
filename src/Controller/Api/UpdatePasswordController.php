<?php

namespace App\Controller\Api;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class UpdatePasswordController extends AbstractController
{
    #[Route(
        '/api/update-password',
        name: 'api_update_password',
        methods: ['PUT']
    )]
    public function updatePassword(

        Request $request,

        EntityManagerInterface $entityManager,

        UserPasswordHasherInterface $passwordHasher,

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
         | REQUEST DATA
         |--------------------------------------------------------------------------
         */
        $data = json_decode(
            $request->getContent(),
            true
        );

        $currentPassword =
            $data['currentPassword'] ?? null;

        $newPassword =
            $data['newPassword'] ?? null;

        /*
         |--------------------------------------------------------------------------
         | VALIDATION
         |--------------------------------------------------------------------------
         */
        if (
            empty($currentPassword)
            || empty($newPassword)
        ) {

            return $this->json([
                'message' =>
                    'Tous les champs sont obligatoires'
            ], 400);
        }

        if (
            strlen($newPassword) < 8
        ) {

            return $this->json([
                'message' =>
                    'Le mot de passe doit contenir au moins 8 caractères'
            ], 400);
        }

        /*
         |--------------------------------------------------------------------------
         | CURRENT PASSWORD CHECK
         |--------------------------------------------------------------------------
         */
        if (
            !$passwordHasher->isPasswordValid(
                $user,
                $currentPassword
            )
        ) {

            return $this->json([
                'message' =>
                    'Mot de passe actuel incorrect'
            ], 400);
        }

        /*
         |--------------------------------------------------------------------------
         | UPDATE PASSWORD
         |--------------------------------------------------------------------------
         */
        $hashedPassword =
            $passwordHasher->hashPassword(
                $user,
                $newPassword
            );

        $user->setPassword(
            $hashedPassword
        );

        $entityManager->persist(
            $user
        );

        $entityManager->flush();

        /*
         |--------------------------------------------------------------------------
         | RESPONSE
         |--------------------------------------------------------------------------
         */
        return $this->json([
            'message' =>
                'Mot de passe mis à jour avec succès'
        ]);
    }
}