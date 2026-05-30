<?php

namespace App\Controller\Api;

use App\Entity\User;
use App\Entity\CandidateProfile;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class CandidateProfileApiController extends AbstractController
{
    #[Route(
        '/api/candidate-profile',
        name: 'api_candidate_profile',
        methods: ['GET']
    )]
    public function getProfile(
        #[CurrentUser]
        ?User $user
    ): JsonResponse {

        if (!$user) {

            return $this->json([
                'message' => 'Unauthorized'
            ], 401);
        }

        $profile =
            $user->getCandidateProfile();

        if (!$profile) {

            return $this->json([
                'message' => 'Candidate profile not found'
            ], 404);
        }

        return $this->json([

            'id' =>
                $profile->getId(),

            'titreProfil' =>
                $profile->getTitreProfil(),

            'bio' =>
                $profile->getBio(),

            'experienceAnnees' =>
                $profile->getExperienceAnnees(),

            'localisation' =>
                $profile->getLocalisation(),

            'disponibilite' =>
                $profile->getDisponibilite(),

            'cvUrl' =>
                $profile->getCvUrl(),
            
            'user' => [

    'id' =>
        $user->getId(),

    'email' =>
        $user->getEmail(),
],

            'skills' => array_map(

                fn($skill) => [

                    'id' =>
                        $skill->getId(),

                    'nom' =>
                        $skill->getNom(),

                    'categorie' =>
                        $skill->getCategorie(),
                ],

                $profile
                    ->getSkills()
                    ->toArray()
            ),
        ]);
    }

    #[Route(
        '/api/candidate-profile',
        name: 'api_candidate_profile_update',
        methods: ['PUT']
    )]
    public function updateProfile(

        Request $request,

        EntityManagerInterface $entityManager,

        #[CurrentUser]
        ?User $user

    ): JsonResponse {

        if (!$user) {

            return $this->json([
                'message' => 'Unauthorized'
            ], 401);
        }

        $profile =
            $user->getCandidateProfile();

        if (!$profile) {

            return $this->json([
                'message' => 'Candidate profile not found'
            ], 404);
        }

        $data = json_decode(
            $request->getContent(),
            true
        );

        if (isset($data['titreProfil'])) {

            $profile->setTitreProfil(
                $data['titreProfil']
            );
        }

        if (isset($data['bio'])) {

            $profile->setBio(
                $data['bio']
            );
        }

        if (
            isset(
                $data['experienceAnnees']
            )
        ) {

            $profile->setExperienceAnnees(
                (int) $data[
                    'experienceAnnees'
                ]
            );
        }

        if (
            isset(
                $data['localisation']
            )
        ) {

            $profile->setLocalisation(
                $data['localisation']
            );
        }

        if (
    isset(
        $data['cvUrl']
    )
) {

    $profile->setCvUrl(
        $data['cvUrl']
    );
}

        $entityManager->flush();

        return $this->json([

            'message' =>
                'Profil candidat mis à jour avec succès'
        ]);
    }
}