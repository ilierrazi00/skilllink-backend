<?php

namespace App\Controller;

use App\Entity\Application;
use App\Form\ApplicationType;
use App\Repository\ApplicationRepository;
use App\Service\SkillMatchingService;
use App\Service\AuditLoggerService;
use App\Service\NotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/application')]
final class ApplicationController extends AbstractController
{
    #[Route(name: 'app_application_index', methods: ['GET'])]
    public function index(
        ApplicationRepository $applicationRepository
    ): Response {

        $this->denyAccessUnlessGranted('ROLE_USER');

        if ($this->isGranted('ROLE_ADMIN')) {

            $applications = $applicationRepository->findAll();

        } elseif ($this->isGranted('ROLE_RECRUITER')) {

            $userCompany = $this->getUser()?->getCompany();

            $allApplications = $applicationRepository->findAll();

            $applications = array_filter(
                $allApplications,
                fn($application) =>
                    $application->getJobOffer()?->getCompany()?->getId()
                    === $userCompany?->getId()
            );

        } else {

            $user = $this->getUser();

            $applications = $applicationRepository->findBy([
                'candidateProfile' => $user?->getCandidateProfile(),
            ]);
        }

        return $this->render('application/index.html.twig', [
            'applications' => $applications,
        ]);
    }

    #[Route('/new', name: 'app_application_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        SkillMatchingService $skillMatchingService,
        AuditLoggerService $auditLoggerService,
        NotificationService $notificationService
    ): Response {

        if (
            !$this->isGranted('ROLE_CANDIDATE')
            && !$this->isGranted('ROLE_ADMIN')
        ) {
            throw $this->createAccessDeniedException(
                'Création réservée aux candidats ou administrateurs.'
            );
        }

        $application = new Application();

        if (
            $this->isGranted('ROLE_CANDIDATE')
            && !$this->isGranted('ROLE_ADMIN')
        ) {

            $candidateProfile = $this->getUser()?->getCandidateProfile();

            if ($candidateProfile) {
                $application->setCandidateProfile($candidateProfile);
            }
        }

        $form = $this->createForm(
            ApplicationType::class,
            $application
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $matchScore = $skillMatchingService->calculateMatchScore(
                $application->getCandidateProfile(),
                $application->getJobOffer()
            );

            if ($matchScore >= 70) {

                $this->addFlash(
                    'success',
                    "Excellent matching détecté : {$matchScore}% de compatibilité."
                );

            } else {

                $this->addFlash(
                    'notice',
                    "Matching estimé : {$matchScore}%."
                );
            }

            $entityManager->persist($application);
            $entityManager->flush();

            /*
             |------------------------------------------------------------------
             | Audit Log MongoDB
             |------------------------------------------------------------------
             */
            $auditLoggerService->log(
                'CREATE_APPLICATION',
                'Application',
                (string) $application->getId(),
                $this->getUser()?->getUserIdentifier() ?? 'anonymous',
                [
                    'match_score' => $matchScore,
                    'candidate' => $application->getCandidateProfile()->getTitreProfil(),
                    'job_offer' => $application->getJobOffer()->getTitre(),
                    'status' => $application->getStatut(),
                ]
            );

            /*
             |------------------------------------------------------------------
             | Notification MongoDB
             |------------------------------------------------------------------
             */
            $notificationService->createNotification(
                (string) $this->getUser()?->getId(),
                'application_submitted',
                'Candidature envoyée',
                'Votre candidature pour "' .
                $application->getJobOffer()->getTitre() .
                '" a bien été enregistrée avec un score de matching de ' .
                $matchScore .
                '%.',
                [
                    'applicationId' => $application->getId(),
                    'jobOfferId' => $application->getJobOffer()?->getId(),
                    'matchScore' => $matchScore,
                ]
            );

            return $this->redirectToRoute(
                'app_application_index',
                [],
                Response::HTTP_SEE_OTHER
            );
        }

        return $this->render('application/new.html.twig', [
            'application' => $application,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_application_show', methods: ['GET'])]
    public function show(
        Application $application,
        SkillMatchingService $skillMatchingService
    ): Response {

        $this->denyAccessUnlessGranted('ROLE_USER');

        if (
            $this->isGranted('ROLE_CANDIDATE')
            && !$this->isGranted('ROLE_RECRUITER')
            && !$this->isGranted('ROLE_ADMIN')
        ) {

            $userCandidateProfile = $this->getUser()?->getCandidateProfile();

            if (
                !$userCandidateProfile
                || $application->getCandidateProfile()?->getId()
                    !== $userCandidateProfile->getId()
            ) {
                throw $this->createAccessDeniedException(
                    'Accès refusé à cette candidature.'
                );
            }
        }

        if (
            $this->isGranted('ROLE_RECRUITER')
            && !$this->isGranted('ROLE_ADMIN')
        ) {

            $userCompany = $this->getUser()?->getCompany();

            if (
                !$userCompany
                || $application->getJobOffer()?->getCompany()?->getId()
                    !== $userCompany->getId()
            ) {
                throw $this->createAccessDeniedException(
                    'Accès refusé à cette candidature.'
                );
            }
        }

        $matchScore = $skillMatchingService->calculateMatchScore(
            $application->getCandidateProfile(),
            $application->getJobOffer()
        );

        return $this->render('application/show.html.twig', [
            'application' => $application,
            'matchScore' => $matchScore,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_application_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Application $application,
        EntityManagerInterface $entityManager,
        AuditLoggerService $auditLoggerService,
        SkillMatchingService $skillMatchingService
    ): Response {

        if (
            !$this->isGranted('ROLE_RECRUITER')
            && !$this->isGranted('ROLE_ADMIN')
        ) {
            throw $this->createAccessDeniedException(
                'Modification réservée aux recruteurs ou administrateurs.'
            );
        }

        if (!$this->isGranted('ROLE_ADMIN')) {

            $userCompany = $this->getUser()?->getCompany();

            if (
                !$userCompany
                || $application->getJobOffer()?->getCompany()?->getId()
                    !== $userCompany->getId()
            ) {
                throw $this->createAccessDeniedException(
                    'Modification non autorisée.'
                );
            }
        }

        $form = $this->createForm(
            ApplicationType::class,
            $application
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager->flush();

            $matchScore = $skillMatchingService->calculateMatchScore(
                $application->getCandidateProfile(),
                $application->getJobOffer()
            );

            $auditLoggerService->log(
                'UPDATE_APPLICATION',
                'Application',
                (string) $application->getId(),
                $this->getUser()?->getUserIdentifier() ?? 'anonymous',
                [
                    'match_score' => $matchScore,
                    'candidate' => $application->getCandidateProfile()->getTitreProfil(),
                    'job_offer' => $application->getJobOffer()->getTitre(),
                    'status' => $application->getStatut(),
                ]
            );

            $this->addFlash(
                'success',
                'Candidature mise à jour avec succès.'
            );

            return $this->redirectToRoute(
                'app_application_index',
                [],
                Response::HTTP_SEE_OTHER
            );
        }

        return $this->render('application/edit.html.twig', [
            'application' => $application,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_application_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        Application $application,
        EntityManagerInterface $entityManager,
        AuditLoggerService $auditLoggerService
    ): Response {

        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if (
            $this->isCsrfTokenValid(
                'delete' . $application->getId(),
                $request->getPayload()->getString('_token')
            )
        ) {

            $auditLoggerService->log(
                'DELETE_APPLICATION',
                'Application',
                (string) $application->getId(),
                $this->getUser()?->getUserIdentifier() ?? 'anonymous',
                [
                    'candidate' => $application->getCandidateProfile()->getTitreProfil(),
                    'job_offer' => $application->getJobOffer()->getTitre(),
                    'status' => $application->getStatut(),
                ]
            );

            $entityManager->remove($application);
            $entityManager->flush();

            $this->addFlash(
                'success',
                'Candidature supprimée avec succès.'
            );
        }

        return $this->redirectToRoute(
            'app_application_index',
            [],
            Response::HTTP_SEE_OTHER
        );
    }
}