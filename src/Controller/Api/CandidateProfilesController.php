<?php

namespace App\Controller\Api;

use App\Repository\CandidateProfileRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class CandidateProfilesController extends AbstractController
{
    #[Route(
        '/api/candidate-profiles',
        name: 'api_candidate_profiles',
        methods: ['GET']
    )]
    public function index(
        CandidateProfileRepository $candidateProfileRepository
    ): JsonResponse {

        $profiles =
            $candidateProfileRepository
                ->findAll();

        $data = [];

        foreach ($profiles as $profile) {

            $user =
                $profile->getUser();

            $data[] = [

                'id' =>
                    $profile->getId(),

                'titreProfil' =>
                    $profile->getTitreProfil(),

                'bio' =>
                    $profile->getBio(),

                'localisation' =>
                    $profile->getLocalisation(),

                'experienceAnnees' =>
                    $profile->getExperienceAnnees(),

                'disponibilite' =>
                    $profile->getDisponibilite(),

                'cvUrl' =>
                    $profile->getCvUrl(),

                'user' => [

                    'id' =>
                        $user?->getId(),

                    'nom' =>
                        $user?->getNom(),

                    'prenom' =>
                        $user?->getPrenom(),

                    'email' =>
                        $user?->getEmail(),
                ],
            ];
        }

        return $this->json(
            $data
        );
    }
}