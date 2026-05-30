<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ChangePasswordFormType;
use App\Form\ResetPasswordRequestFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use SymfonyCasts\Bundle\ResetPassword\Controller\ResetPasswordControllerTrait;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

class ResetPasswordController extends AbstractController
{
    use ResetPasswordControllerTrait;

    public function __construct(
        private ResetPasswordHelperInterface $resetPasswordHelper,
        private EntityManagerInterface $entityManager,
        private MailerInterface $mailer
    ) {
    }

    #[Route('/forgot-password', name: 'app_forgot_password_request')]
    public function request(Request $request): Response
    {
        $form = $this->createForm(ResetPasswordRequestFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->processSendingPasswordResetEmail(
                $form->get('email')->getData()
            );
        }

        return $this->render('reset_password/request.html.twig', [
            'requestForm' => $form->createView(),
        ]);
    }

    private function processSendingPasswordResetEmail(
        string $emailFormData
    ): RedirectResponse {

        /*
         |------------------------------------------------------------------
         | Recherche utilisateur
         |------------------------------------------------------------------
         */
        $user = $this->entityManager
            ->getRepository(User::class)
            ->findOneBy([
                'email' => $emailFormData,
            ]);

        /*
         |------------------------------------------------------------------
         | Sécurité :
         | Même comportement si utilisateur inexistant
         |------------------------------------------------------------------
         */
        if (!$user) {
            return $this->redirectToRoute('app_check_email');
        }

        /*
         |------------------------------------------------------------------
         | Nettoyage anciennes demandes reset
         |------------------------------------------------------------------
         */
        $this->entityManager->createQuery(
            'DELETE FROM App\Entity\ResetPasswordRequest r WHERE r.user = :user'
        )
            ->setParameter('user', $user)
            ->execute();

        /*
         |------------------------------------------------------------------
         | Génération token
         |------------------------------------------------------------------
         */
        try {
            $resetToken = $this->resetPasswordHelper
                ->generateResetToken($user);

        } catch (ResetPasswordExceptionInterface $e) {
            return $this->redirectToRoute('app_check_email');
        }

        /*
         |------------------------------------------------------------------
         | Stockage token session
         |------------------------------------------------------------------
         */
        $this->storeTokenInSession(
            $resetToken->getToken()
        );

        /*
         |------------------------------------------------------------------
         | Génération URL ABSOLUE correcte
         |------------------------------------------------------------------
         */
        $resetUrl = $this->generateUrl(
            'app_reset_password',
            ['token' => $resetToken->getToken()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        /*
         |------------------------------------------------------------------
         | Construction email
         |------------------------------------------------------------------
         */
        $email = (new TemplatedEmail())
            ->from(
                new Address(
                    'ilias.errazi@ecoles-epsi.net',
                    'SkillLink Security'
                )
            )
            ->replyTo('ilias.errazi@ecoles-epsi.net')
            ->to((string) $user->getEmail())
            ->subject('Réinitialisation de votre mot de passe SkillLink')
            ->htmlTemplate('reset_password/email.html.twig')
            ->context([
                'resetToken' => $resetToken,
                'resetUrl' => $resetUrl,
                'user' => $user,
            ]);

        /*
         |------------------------------------------------------------------
         | Envoi final
         |------------------------------------------------------------------
         */
        try {
            $this->mailer->send($email);

            $this->addFlash(
                'success',
                'Un email de réinitialisation vous a été envoyé.'
            );

        } catch (\Exception $e) {

            $this->addFlash(
                'reset_password_error',
                'Erreur lors de l’envoi de l’email : ' . $e->getMessage()
            );

        }

        return $this->redirectToRoute('app_check_email');
    }

    #[Route('/reset-password/check-email', name: 'app_check_email')]
    public function checkEmail(): Response
    {
        if (!$resetToken = $this->getTokenObjectFromSession()) {
            $resetToken = $this->resetPasswordHelper
                ->generateFakeResetToken();
        }

        return $this->render('reset_password/check_email.html.twig', [
            'resetToken' => $resetToken,
        ]);
    }

    #[Route('/reset-password/reset/{token}', name: 'app_reset_password')]
    public function reset(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        ?string $token = null
    ): Response {

        /*
         |------------------------------------------------------------------
         | Stockage token URL
         |------------------------------------------------------------------
         */
        if ($token) {
            $this->storeTokenInSession($token);

            return $this->redirectToRoute('app_reset_password');
        }

        $token = $this->getTokenFromSession();

        if (null === $token) {
            throw $this->createNotFoundException(
                'No reset password token found in the URL or in the session.'
            );
        }

        /*
         |------------------------------------------------------------------
         | Validation token
         |------------------------------------------------------------------
         */
        try {
            $user = $this->resetPasswordHelper
                ->validateTokenAndFetchUser($token);

        } catch (ResetPasswordExceptionInterface $e) {

            $this->addFlash(
                'reset_password_error',
                sprintf(
                    '%s - %s',
                    ResetPasswordExceptionInterface::MESSAGE_PROBLEM_VALIDATE,
                    $e->getReason()
                )
            );

            return $this->redirectToRoute(
                'app_forgot_password_request'
            );
        }

        $form = $this->createForm(ChangePasswordFormType::class);
        $form->handleRequest($request);

        /*
         |------------------------------------------------------------------
         | Validation nouveau mot de passe
         |------------------------------------------------------------------
         */
        if ($form->isSubmitted() && $form->isValid()) {

            $this->resetPasswordHelper
                ->removeResetRequest($token);

            $hashedPassword = $passwordHasher->hashPassword(
                $user,
                $form->get('plainPassword')->getData()
            );

            $user->setPassword($hashedPassword);

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            /*
             |------------------------------------------------------------------
             | Nettoyage session
             |------------------------------------------------------------------
             */
            $this->cleanSessionAfterReset();

            $this->addFlash(
                'success',
                'Votre mot de passe a été réinitialisé avec succès.'
            );

            return $this->redirectToRoute('app_login');
        }

        return $this->render('reset_password/reset.html.twig', [
            'resetForm' => $form->createView(),
        ]);
    }
}