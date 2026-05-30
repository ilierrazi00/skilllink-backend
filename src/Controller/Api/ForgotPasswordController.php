<?php

namespace App\Controller\Api;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class ForgotPasswordController extends AbstractController
{
    #[Route(
        '/api/forgot-password',
        name: 'api_forgot_password',
        methods: ['POST']
    )]
    public function forgotPassword(
        Request $request,
        EntityManagerInterface $entityManager
    ): JsonResponse {

        $data = json_decode(
            $request->getContent(),
            true
        );

        $email = $data['email'] ?? null;

        if (!$email) {

            return $this->json([
                'message' => 'Email requis'
            ], 400);
        }

        $user = $entityManager
            ->getRepository(User::class)
            ->findOneBy([
                'email' => $email
            ]);

        if (!$user) {

            return $this->json([
                'message' =>
                    'Si cet email existe, un lien de réinitialisation a été envoyé.'
            ]);
        }

        /*
         |--------------------------------------------------------------
         | Ici tu pourras plus tard brancher
         | ResetPasswordHelperInterface
         | et EmailVerifier
         |--------------------------------------------------------------
         */

        return $this->json([
            'message' =>
                'Si cet email existe, un lien de réinitialisation a été envoyé.'
        ]);
    }
}