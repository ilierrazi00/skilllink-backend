<?php

namespace App\Controller;

use App\Form\PasswordSettingsFormType;
use App\Form\ProfileSettingsFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/settings')]
class SettingsController extends AbstractController
{
    #[Route('/', name: 'app_settings')]
    public function index(
        Request $request,
        EntityManagerInterface $entityManager,
        Security $security,
        UserPasswordHasherInterface $passwordHasher
    ): Response {

        /*
         |--------------------------------------------------------------------------
         | Sécurité utilisateur connecté
         |--------------------------------------------------------------------------
         */
        $user = $security->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        /*
         |--------------------------------------------------------------------------
         | FORMULAIRE PROFIL
         |--------------------------------------------------------------------------
         */
        $profileForm = $this->createForm(
            ProfileSettingsFormType::class,
            $user
        );

        $profileForm->handleRequest($request);

        /*
         |--------------------------------------------------------------------------
         | Sauvegarde profil
         |--------------------------------------------------------------------------
         */
        if (
            $profileForm->isSubmitted()
            && $profileForm->isValid()
        ) {

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash(
                'success',
                'Vos informations de profil ont été mises à jour avec succès.'
            );

            return $this->redirectToRoute(
                'app_settings'
            );
        }

        /*
         |--------------------------------------------------------------------------
         | FORMULAIRE MOT DE PASSE
         |--------------------------------------------------------------------------
         */
        $passwordForm = $this->createForm(
            PasswordSettingsFormType::class
        );

        $passwordForm->handleRequest($request);

        /*
         |--------------------------------------------------------------------------
         | Sauvegarde mot de passe
         |--------------------------------------------------------------------------
         */
        if (
            $passwordForm->isSubmitted()
            && $passwordForm->isValid()
        ) {

            /*
             |----------------------------------------------------------------------
             | Vérification mot de passe actuel
             |----------------------------------------------------------------------
             */
            $currentPassword = $passwordForm
                ->get('currentPassword')
                ->getData();

            if (
                !$passwordHasher->isPasswordValid(
                    $user,
                    $currentPassword
                )
            ) {

                $this->addFlash(
                    'warning',
                    'Le mot de passe actuel est incorrect.'
                );

                return $this->redirectToRoute(
                    'app_settings'
                );
            }

            /*
             |----------------------------------------------------------------------
             | Nouveau mot de passe
             |----------------------------------------------------------------------
             */
            $newPassword = $passwordForm
                ->get('newPassword')
                ->getData();

            /*
             |----------------------------------------------------------------------
             | Hash sécurisé
             |----------------------------------------------------------------------
             */
            $hashedPassword = $passwordHasher->hashPassword(
                $user,
                $newPassword
            );

            $user->setPassword(
                $hashedPassword
            );

            /*
             |----------------------------------------------------------------------
             | Sauvegarde
             |----------------------------------------------------------------------
             */
            $entityManager->persist($user);
            $entityManager->flush();

            /*
             |----------------------------------------------------------------------
             | Succès
             |----------------------------------------------------------------------
             */
            $this->addFlash(
                'success',
                'Votre mot de passe a été modifié avec succès.'
            );

            return $this->redirectToRoute(
                'app_settings'
            );
        }

        /*
         |--------------------------------------------------------------------------
         | Render page paramètres premium
         |--------------------------------------------------------------------------
         */
        return $this->render(
            'settings/index.html.twig',
            [
                'settingsForm' => $profileForm->createView(),
                'passwordForm' => $passwordForm->createView(),
                'user' => $user,
            ]
        );
    }
}