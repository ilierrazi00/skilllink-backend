<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(
        AuthenticationUtils $authenticationUtils
    ): Response {
        /*
         |--------------------------------------------------------------------------
         | Si l'utilisateur est déjà connecté,
         | redirection intelligente selon son rôle
         |--------------------------------------------------------------------------
         */
        if ($this->getUser()) {
            return $this->redirectToRoute('app_dashboard');
        }

        /*
         |--------------------------------------------------------------------------
         | Dernière erreur d'authentification
         |--------------------------------------------------------------------------
         */
        $error = $authenticationUtils->getLastAuthenticationError();

        /*
         |--------------------------------------------------------------------------
         | Dernier identifiant saisi
         |--------------------------------------------------------------------------
         */
        $lastUsername = $authenticationUtils->getLastUsername();

        /*
         |--------------------------------------------------------------------------
         | Affichage page login
         |--------------------------------------------------------------------------
         */
        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        /*
         |--------------------------------------------------------------------------
         | Intercepté automatiquement par Symfony Security
         |--------------------------------------------------------------------------
         */
        throw new \LogicException(
            'Cette méthode est gérée automatiquement par Symfony Security.'
        );
    }
}