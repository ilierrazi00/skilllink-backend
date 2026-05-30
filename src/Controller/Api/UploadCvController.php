<?php

namespace App\Controller\Api;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class UploadCvController extends AbstractController
{
    #[Route('/api/upload-cv', name: 'api_upload_cv', methods: ['POST'])]
    public function uploadCv(
        Request $request,
        EntityManagerInterface $entityManager,
        #[CurrentUser] ?User $user
    ): JsonResponse {

        if (!$user) {
            return $this->json([
                'message' => 'Unauthorized'
            ], 401);
        }

        $file = $request->files->get('cv');

        if (!$file) {
            return $this->json([
                'message' => 'Aucun fichier reçu'
            ], 400);
        }

        if ($file->getClientOriginalExtension() !== 'pdf') {
            return $this->json([
                'message' => 'Seuls les fichiers PDF sont acceptés'
            ], 400);
        }

        $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/cv';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileName = 'cv_user_' . $user->getId() . '_' . uniqid() . '.pdf';

        $file->move(
            $uploadDir,
            $fileName
        );

        // SAUVEGARDE EN BASE
        $cvPath = '/uploads/cv/' . $fileName;

        $user->setCvPath($cvPath);

        $entityManager->flush();

        return $this->json([
            'message' => 'CV uploadé avec succès',
            'fileName' => $fileName,
            'path' => $cvPath,
        ]);
    }
}