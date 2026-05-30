<?php

namespace App\Controller;

use App\Entity\Skill;
use App\Form\SkillType;
use App\Repository\SkillRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/skill')]
final class SkillController extends AbstractController
{
    #[Route(name: 'app_skill_index', methods: ['GET'])]
    public function index(
        SkillRepository $skillRepository
    ): Response {
        /*
         |------------------------------------------------------------------
         | Tous les rôles connectés peuvent consulter
         | Candidate / Recruiter / Admin
         |------------------------------------------------------------------
         */
        $this->denyAccessUnlessGranted('ROLE_USER');

        return $this->render('skill/index.html.twig', [
            'skills' => $skillRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_skill_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        /*
         |------------------------------------------------------------------
         | Création : Admin uniquement
         |------------------------------------------------------------------
         */
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $skill = new Skill();
        $form = $this->createForm(
            SkillType::class,
            $skill
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager->persist($skill);
            $entityManager->flush();

            $this->addFlash(
                'success',
                'Compétence ajoutée avec succès.'
            );

            return $this->redirectToRoute(
                'app_skill_index',
                [],
                Response::HTTP_SEE_OTHER
            );
        }

        return $this->render('skill/new.html.twig', [
            'skill' => $skill,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_skill_show', methods: ['GET'])]
    public function show(
        Skill $skill
    ): Response {
        /*
         |------------------------------------------------------------------
         | Consultation : Tous les utilisateurs connectés
         |------------------------------------------------------------------
         */
        $this->denyAccessUnlessGranted('ROLE_USER');

        return $this->render('skill/show.html.twig', [
            'skill' => $skill,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_skill_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Skill $skill,
        EntityManagerInterface $entityManager
    ): Response {
        /*
         |------------------------------------------------------------------
         | Modification : Admin uniquement
         |------------------------------------------------------------------
         */
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $form = $this->createForm(
            SkillType::class,
            $skill
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager->flush();

            $this->addFlash(
                'success',
                'Compétence mise à jour avec succès.'
            );

            return $this->redirectToRoute(
                'app_skill_index',
                [],
                Response::HTTP_SEE_OTHER
            );
        }

        return $this->render('skill/edit.html.twig', [
            'skill' => $skill,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_skill_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        Skill $skill,
        EntityManagerInterface $entityManager
    ): Response {
        /*
         |------------------------------------------------------------------
         | Suppression : Admin uniquement
         |------------------------------------------------------------------
         */
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if (
            $this->isCsrfTokenValid(
                'delete' . $skill->getId(),
                $request->getPayload()->getString('_token')
            )
        ) {
            $entityManager->remove($skill);
            $entityManager->flush();

            $this->addFlash(
                'success',
                'Compétence supprimée avec succès.'
            );
        }

        return $this->redirectToRoute(
            'app_skill_index',
            [],
            Response::HTTP_SEE_OTHER
        );
    }
}