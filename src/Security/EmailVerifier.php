<?php

namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

class EmailVerifier
{
    public function __construct(
        private VerifyEmailHelperInterface $verifyEmailHelper,
        private MailerInterface $mailer,
        private EntityManagerInterface $entityManager,
    ) {
    }

    /*
     |--------------------------------------------------------------------------
     | ENVOI EMAIL DE CONFIRMATION
     |--------------------------------------------------------------------------
     */
    public function sendEmailConfirmation(
        string $verifyEmailRouteName,
        User $user,
        TemplatedEmail $email
    ): void {

        /*
         |--------------------------------------------------------------------------
         | Génération signature sécurisée
         |--------------------------------------------------------------------------
         */
        $signatureComponents = $this->verifyEmailHelper->generateSignature(
            $verifyEmailRouteName,
            (string) $user->getId(),
            (string) $user->getEmail(),
            ['id' => $user->getId()]
        );

        /*
         |--------------------------------------------------------------------------
         | Injection contexte sécurisé
         |--------------------------------------------------------------------------
         */
        $context = $email->getContext();

        $context['signedUrl'] = $signatureComponents->getSignedUrl();
        $context['expiresAtMessageKey'] = $signatureComponents->getExpirationMessageKey();
        $context['expiresAtMessageData'] = $signatureComponents->getExpirationMessageData();
        $context['user'] = $user;

        $email->context($context);

        /*
         |--------------------------------------------------------------------------
         | Envoi final
         |--------------------------------------------------------------------------
         */
        $this->mailer->send($email);
    }

    /**
     * @throws VerifyEmailExceptionInterface
     */
    public function handleEmailConfirmation(
        Request $request,
        User $user
    ): void {

        /*
         |--------------------------------------------------------------------------
         | Validation signature
         |--------------------------------------------------------------------------
         */
        $this->verifyEmailHelper->validateEmailConfirmationFromRequest(
            $request,
            (string) $user->getId(),
            (string) $user->getEmail()
        );

        /*
         |--------------------------------------------------------------------------
         | Activation compte
         |--------------------------------------------------------------------------
         */
        $user->setIsVerified(true);

        /*
         |--------------------------------------------------------------------------
         | Sauvegarde
         |--------------------------------------------------------------------------
         */
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
}