<?php

namespace App\Controller;

use App\Entity\Company;
use App\Form\CompanyType;
use App\Repository\CompanyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/company')]
final class CompanyController extends AbstractController
{
    #[Route(name: 'app_company_index', methods: ['GET'])]
    public function index(
        CompanyRepository $companyRepository
    ): Response {
        /*
         |------------------------------------------------------------------
         | Consultation :
         | Recruiter + Admin uniquement
         |------------------------------------------------------------------
         */
        $this->denyAccessUnlessGranted('ROLE_RECRUITER');

        return $this->render('company/index.html.twig', [
            'companies' => $companyRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_company_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        /*
         |------------------------------------------------------------------
         | Création :
         | Recruiter + Admin
         |------------------------------------------------------------------
         */
        $this->denyAccessUnlessGranted('ROLE_RECRUITER');

        $company = new Company();

        $form = $this->createForm(
            CompanyType::class,
            $company
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager->persist($company);
            $entityManager->flush();

            $this->addFlash(
                'success',
                'Entreprise créée avec succès.'
            );

            return $this->redirectToRoute(
                'app_company_index',
                [],
                Response::HTTP_SEE_OTHER
            );
        }

        return $this->render('company/new.html.twig', [
            'company' => $company,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_company_show', methods: ['GET'])]
    public function show(
        Company $company
    ): Response {
        /*
         |------------------------------------------------------------------
         | Consultation détail :
         | Tous les utilisateurs connectés
         | Candidate peut consulter
         |------------------------------------------------------------------
         */
        $this->denyAccessUnlessGranted('ROLE_USER');

        return $this->render('company/show.html.twig', [
            'company' => $company,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_company_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Company $company,
        EntityManagerInterface $entityManager
    ): Response {
        /*
         |------------------------------------------------------------------
         | Modification :
         | Recruiter + Admin
         |------------------------------------------------------------------
         */
        $this->denyAccessUnlessGranted('ROLE_RECRUITER');

        $form = $this->createForm(
            CompanyType::class,
            $company
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager->flush();

            $this->addFlash(
                'success',
                'Entreprise mise à jour avec succès.'
            );

            return $this->redirectToRoute(
                'app_company_index',
                [],
                Response::HTTP_SEE_OTHER
            );
        }

        return $this->render('company/edit.html.twig', [
            'company' => $company,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_company_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        Company $company,
        EntityManagerInterface $entityManager
    ): Response {
        /*
         |------------------------------------------------------------------
         | Suppression :
         | Admin uniquement
         |------------------------------------------------------------------
         */
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if (
            $this->isCsrfTokenValid(
                'delete' . $company->getId(),
                $request->getPayload()->getString('_token')
            )
        ) {
            $entityManager->remove($company);
            $entityManager->flush();

            $this->addFlash(
                'success',
                'Entreprise supprimée avec succès.'
            );
        }

        return $this->redirectToRoute(
            'app_company_index',
            [],
            Response::HTTP_SEE_OTHER
        );
    }
}