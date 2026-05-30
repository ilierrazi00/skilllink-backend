<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{
    public function __construct(
        private EmailVerifier $emailVerifier
    ) {
    }

    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        Security $security,
        EntityManagerInterface $entityManager
    ): Response {

        /*
         |--------------------------------------------------------------------------
         | Si utilisateur déjà connecté
         |--------------------------------------------------------------------------
         */
        if ($this->getUser()) {
            return $this->redirectToRoute('app_dashboard');
        }

        $user = new User();

        $form = $this->createForm(
            RegistrationFormType::class,
            $user
        );

        $form->handleRequest($request);

        /*
         |--------------------------------------------------------------------------
         | FORMULAIRE VALIDE
         |--------------------------------------------------------------------------
         */
        if ($form->isSubmitted() && $form->isValid()) {

            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();

            /** @var string $selectedRole */
            $selectedRole = $form->get('roleType')->getData();

            /*
             |--------------------------------------------------------------------------
             | HASH PASSWORD
             |--------------------------------------------------------------------------
             */
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $plainPassword
                )
            );

            /*
             |--------------------------------------------------------------------------
             | ROLE CHOISI À L’INSCRIPTION
             |--------------------------------------------------------------------------
             */
            $user->setRoleType(
                $selectedRole ?: 'candidate'
            );

            /*
             |--------------------------------------------------------------------------
             | EMAIL NON VÉRIFIÉ PAR DÉFAUT
             |--------------------------------------------------------------------------
             */
            $user->setIsVerified(false);

            /*
             |--------------------------------------------------------------------------
             | CONSENTEMENTS
             |--------------------------------------------------------------------------
             */
            $user->setAcceptedTermsAt(
                new \DateTimeImmutable()
            );

            $user->setAcceptedPrivacyAt(
                new \DateTimeImmutable()
            );

            /*
             |--------------------------------------------------------------------------
             | PERSIST
             |--------------------------------------------------------------------------
             */
            $entityManager->persist($user);
            $entityManager->flush();

            /*
             |--------------------------------------------------------------------------
             | EMAIL VERIFICATION
             |--------------------------------------------------------------------------
             */
            $this->emailVerifier->sendEmailConfirmation(
                'app_verify_email',
                $user,
                (new TemplatedEmail())
                    ->from(
                        new Address(
                            'ilias.errazi@ecoles-epsi.net',
                            'SkillLink Security'
                        )
                    )
                    ->replyTo(
                        'ilias.errazi@ecoles-epsi.net'
                    )
                    ->to((string) $user->getEmail())
                    ->subject(
                        'Confirmez votre adresse email SkillLink'
                    )
                    ->htmlTemplate(
                        'registration/confirmation_email.html.twig'
                    )
                    ->context([
                        'user' => $user,
                    ])
            );

            /*
             |--------------------------------------------------------------------------
             | FLASH MESSAGE
             |--------------------------------------------------------------------------
             */
            $this->addFlash(
                'success',
                'Compte créé avec succès. Vérifiez votre adresse email avant de vous connecter.'
            );

            /*
             |--------------------------------------------------------------------------
             | REDIRECTION LOGIN
             |--------------------------------------------------------------------------
             */
            return $this->redirectToRoute(
                'app_login'
            );
        }

        /*
         |--------------------------------------------------------------------------
         | RENDER
         |--------------------------------------------------------------------------
         */
        return $this->render(
            'registration/register.html.twig',
            [
                'registrationForm' => $form,
            ]
        );
    }

    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(
        Request $request,
        TranslatorInterface $translator
    ): Response {

        $this->denyAccessUnlessGranted(
            'IS_AUTHENTICATED_FULLY'
        );

        try {

            /** @var User $user */
            $user = $this->getUser();

            $this->emailVerifier->handleEmailConfirmation(
                $request,
                $user
            );

        } catch (VerifyEmailExceptionInterface $exception) {

            $this->addFlash(
                'verify_email_error',
                $translator->trans(
                    $exception->getReason(),
                    [],
                    'VerifyEmailBundle'
                )
            );

            return $this->redirectToRoute(
                'app_register'
            );
        }

        /*
         |--------------------------------------------------------------------------
         | EMAIL VERIFIED
         |--------------------------------------------------------------------------
         */
        $this->addFlash(
            'success',
            'Votre adresse email a bien été vérifiée. Vous pouvez maintenant vous connecter.'
        );

        return $this->redirectToRoute(
            'app_login'
        );
    }
}