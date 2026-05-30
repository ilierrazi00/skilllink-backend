<?php

namespace App\Controller;

use App\Entity\JobOffer;
use App\Form\JobOfferType;
use App\Repository\JobOfferRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/job/offer')]
final class JobOfferController extends AbstractController
{
    #[Route(name: 'app_job_offer_index', methods: ['GET'])]
    public function index(
        JobOfferRepository $jobOfferRepository
    ): Response {

        /*
         |------------------------------------------------------------------
         | Tous les utilisateurs connectés peuvent consulter
         |------------------------------------------------------------------
         */
        $this->denyAccessUnlessGranted('ROLE_USER');

        /*
         |------------------------------------------------------------------
         | ADMIN : toutes les offres
         |------------------------------------------------------------------
         */
        if ($this->isGranted('ROLE_ADMIN')) {

            $jobOffers = $jobOfferRepository->findAll();
        }

        /*
         |------------------------------------------------------------------
         | RECRUITER : uniquement ses propres offres
         |------------------------------------------------------------------
         */
        elseif ($this->isGranted('ROLE_RECRUITER')) {

            $userCompany = $this->getUser()?->getCompany();

            $jobOffers = $jobOfferRepository->findBy([
                'company' => $userCompany,
            ]);
        }

        /*
         |------------------------------------------------------------------
         | CANDIDATE : consultation globale
         |------------------------------------------------------------------
         */
        else {

            $jobOffers = $jobOfferRepository->findAll();
        }

        return $this->render('job_offer/index.html.twig', [
            'job_offers' => $jobOffers,
        ]);
    }

    #[Route('/new', name: 'app_job_offer_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {

        /*
         |------------------------------------------------------------------
         | RECRUITER + ADMIN uniquement
         |------------------------------------------------------------------
         */
        if (
            !$this->isGranted('ROLE_RECRUITER')
            && !$this->isGranted('ROLE_ADMIN')
        ) {
            throw $this->createAccessDeniedException(
                'Création réservée aux recruteurs ou administrateurs.'
            );
        }

        $jobOffer = new JobOffer();

        /*
         |------------------------------------------------------------------
         | Auto association entreprise recruteur
         |------------------------------------------------------------------
         */
        if (
            $this->isGranted('ROLE_RECRUITER')
            && !$this->isGranted('ROLE_ADMIN')
        ) {

            $userCompany = $this->getUser()?->getCompany();

            if ($userCompany) {
                $jobOffer->setCompany($userCompany);
            }
        }

        $form = $this->createForm(
            JobOfferType::class,
            $jobOffer
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager->persist($jobOffer);
            $entityManager->flush();

            $this->addFlash(
                'success',
                'Offre créée avec succès.'
            );

            return $this->redirectToRoute(
                'app_job_offer_index',
                [],
                Response::HTTP_SEE_OTHER
            );
        }

        return $this->render('job_offer/new.html.twig', [
            'job_offer' => $jobOffer,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_job_offer_show', methods: ['GET'])]
    public function show(
        JobOffer $jobOffer
    ): Response {

        /*
         |------------------------------------------------------------------
         | Tous les utilisateurs connectés peuvent consulter
         |------------------------------------------------------------------
         */
        $this->denyAccessUnlessGranted('ROLE_USER');

        /*
         |------------------------------------------------------------------
         | RECRUITER : contrôle de propriété
         |------------------------------------------------------------------
         */
        if (
            $this->isGranted('ROLE_RECRUITER')
            && !$this->isGranted('ROLE_ADMIN')
        ) {

            $userCompany = $this->getUser()?->getCompany();

            if (
                !$userCompany
                || $jobOffer->getCompany()?->getId()
                    !== $userCompany->getId()
            ) {
                throw $this->createAccessDeniedException(
                    'Accès refusé à cette offre.'
                );
            }
        }

        return $this->render('job_offer/show.html.twig', [
            'job_offer' => $jobOffer,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_job_offer_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        JobOffer $jobOffer,
        EntityManagerInterface $entityManager
    ): Response {

        /*
         |------------------------------------------------------------------
         | RECRUITER + ADMIN uniquement
         |------------------------------------------------------------------
         */
        if (
            !$this->isGranted('ROLE_RECRUITER')
            && !$this->isGranted('ROLE_ADMIN')
        ) {
            throw $this->createAccessDeniedException(
                'Modification réservée aux recruteurs ou administrateurs.'
            );
        }

        /*
         |------------------------------------------------------------------
         | RECRUITER : contrôle propriété
         |------------------------------------------------------------------
         */
        if (!$this->isGranted('ROLE_ADMIN')) {

            $userCompany = $this->getUser()?->getCompany();

            if (
                !$userCompany
                || $jobOffer->getCompany()?->getId()
                    !== $userCompany->getId()
            ) {
                throw $this->createAccessDeniedException(
                    'Modification non autorisée.'
                );
            }
        }

        $form = $this->createForm(
            JobOfferType::class,
            $jobOffer
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager->flush();

            $this->addFlash(
                'success',
                'Offre mise à jour avec succès.'
            );

            return $this->redirectToRoute(
                'app_job_offer_index',
                [],
                Response::HTTP_SEE_OTHER
            );
        }

        return $this->render('job_offer/edit.html.twig', [
            'job_offer' => $jobOffer,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_job_offer_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        JobOffer $jobOffer,
        EntityManagerInterface $entityManager
    ): Response {

        /*
         |------------------------------------------------------------------
         | ADMIN uniquement pour suppression complète
         |------------------------------------------------------------------
         */
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if (
            $this->isCsrfTokenValid(
                'delete' . $jobOffer->getId(),
                $request->getPayload()->getString('_token')
            )
        ) {

            $entityManager->remove($jobOffer);
            $entityManager->flush();

            $this->addFlash(
                'success',
                'Offre supprimée avec succès.'
            );
        }

        return $this->redirectToRoute(
            'app_job_offer_index',
            [],
            Response::HTTP_SEE_OTHER
        );
    }
}