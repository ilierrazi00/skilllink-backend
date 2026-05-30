<?php

namespace App\Controller;

use App\Entity\CandidateProfile;
use App\Form\CandidateProfileType;
use App\Repository\CandidateProfileRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/candidate/profile')]
final class CandidateProfileController extends AbstractController
{
    #[Route(name: 'app_candidate_profile_index', methods: ['GET'])]
    public function index(
        CandidateProfileRepository $candidateProfileRepository
    ): Response {
        $user = $this->getUser();

        /*
         |----------------------------------------------------------------------
         | ADMIN / RECRUITER
         |----------------------------------------------------------------------
         */
        if (
            $this->isGranted('ROLE_ADMIN') ||
            $this->isGranted('ROLE_RECRUITER')
        ) {
            $profiles = $candidateProfileRepository->findAll();
        }

        /*
         |----------------------------------------------------------------------
         | CANDIDATE
         |----------------------------------------------------------------------
         */
        else {
            $profiles = [];

            if (
                $user &&
                method_exists($user, 'getCandidateProfile') &&
                $user->getCandidateProfile()
            ) {
                $profiles[] = $user->getCandidateProfile();
            }
        }

        return $this->render('candidate_profile/index.html.twig', [
            'candidate_profiles' => $profiles,
        ]);
    }

    #[Route('/new', name: 'app_candidate_profile_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        /*
         |----------------------------------------------------------------------
         | RESTRICTION
         |----------------------------------------------------------------------
         */
        if (
            !$this->isGranted('ROLE_CANDIDATE') &&
            !$this->isGranted('ROLE_ADMIN')
        ) {
            throw $this->createAccessDeniedException(
                'Accès refusé.'
            );
        }

        $candidateProfile = new CandidateProfile();

        /*
         |----------------------------------------------------------------------
         | AUTO LINK USER
         |----------------------------------------------------------------------
         */
        if (
            $this->getUser() &&
            method_exists($this->getUser(), 'getCandidateProfile') &&
            !$this->getUser()->getCandidateProfile()
        ) {
            $candidateProfile->setUser($this->getUser());
        }

        $form = $this->createForm(
            CandidateProfileType::class,
            $candidateProfile
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager->persist($candidateProfile);
            $entityManager->flush();

            $this->addFlash(
                'success',
                'Profil candidat créé avec succès.'
            );

            return $this->redirectToRoute(
                'app_candidate_profile_index',
                [],
                Response::HTTP_SEE_OTHER
            );
        }

        return $this->render('candidate_profile/new.html.twig', [
            'candidate_profile' => $candidateProfile,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_candidate_profile_show', methods: ['GET'])]
    public function show(
        CandidateProfile $candidateProfile
    ): Response {
        /*
         |----------------------------------------------------------------------
         | SECURITY
         |----------------------------------------------------------------------
         */
        if (
            $this->isGranted('ROLE_CANDIDATE') &&
            $candidateProfile->getUser() !== $this->getUser()
        ) {
            throw $this->createAccessDeniedException(
                'Accès refusé.'
            );
        }

        return $this->render('candidate_profile/show.html.twig', [
            'candidate_profile' => $candidateProfile,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_candidate_profile_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        CandidateProfile $candidateProfile,
        EntityManagerInterface $entityManager
    ): Response {
        /*
         |----------------------------------------------------------------------
         | SECURITY
         |----------------------------------------------------------------------
         */
        if (
            $this->isGranted('ROLE_CANDIDATE') &&
            $candidateProfile->getUser() !== $this->getUser()
        ) {
            throw $this->createAccessDeniedException(
                'Accès refusé.'
            );
        }

        $form = $this->createForm(
            CandidateProfileType::class,
            $candidateProfile
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager->flush();

            $this->addFlash(
                'success',
                'Profil candidat mis à jour.'
            );

            return $this->redirectToRoute(
                'app_candidate_profile_index',
                [],
                Response::HTTP_SEE_OTHER
            );
        }

        return $this->render('candidate_profile/edit.html.twig', [
            'candidate_profile' => $candidateProfile,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_candidate_profile_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        CandidateProfile $candidateProfile,
        EntityManagerInterface $entityManager
    ): Response {
        /*
         |----------------------------------------------------------------------
         | SECURITY
         |----------------------------------------------------------------------
         */
        if (
            !$this->isGranted('ROLE_ADMIN') &&
            $candidateProfile->getUser() !== $this->getUser()
        ) {
            throw $this->createAccessDeniedException(
                'Accès refusé.'
            );
        }

        if (
            $this->isCsrfTokenValid(
                'delete' . $candidateProfile->getId(),
                $request->getPayload()->getString('_token')
            )
        ) {
            $entityManager->remove($candidateProfile);
            $entityManager->flush();

            $this->addFlash(
                'success',
                'Profil candidat supprimé.'
            );
        }

        return $this->redirectToRoute(
            'app_candidate_profile_index',
            [],
            Response::HTTP_SEE_OTHER
        );
    }
}