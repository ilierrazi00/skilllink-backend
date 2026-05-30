<?php

namespace App\Controller\Api;

use App\Entity\User;
use App\Service\MessageService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/api/messages')]
class MessageController extends AbstractController
{
    public function __construct(
        private MessageService $messageService,
        private EntityManagerInterface $entityManager
    ) {}

    /*
     |----------------------------------------------------------------------
     | Envoyer un message
     |----------------------------------------------------------------------
     */
    #[Route('/send', methods: ['POST'])]
    public function sendMessage(
        Request $request,
        #[CurrentUser] ?User $user
    ): JsonResponse {

        if (!$user) {

            return $this->json([
                'message' => 'Unauthorized'
            ], 401);
        }

        $data = json_decode(
            $request->getContent(),
            true
        );

        if (
            !isset($data['receiverId']) ||
            !isset($data['content'])
        ) {

            return $this->json([
                'message' => 'Données manquantes'
            ], 400);
        }

        $receiver = $this->entityManager
            ->getRepository(User::class)
            ->find($data['receiverId']);

        if (!$receiver) {

            return $this->json([
                'message' => 'Utilisateur introuvable'
            ], 404);
        }

        $this->messageService->sendMessage(

            (string) $user->getId(),
            (string) $receiver->getId(),

            $user->getEmail(),
            $receiver->getEmail(),

            $data['content']
        );

        return $this->json([
            'message' => 'Message envoyé avec succès'
        ]);
    }

    /*
     |----------------------------------------------------------------------
     | Récupérer conversation
     |----------------------------------------------------------------------
     */
    #[Route('/conversation/{userId}', methods: ['GET'])]
    public function getConversation(
        string $userId,
        #[CurrentUser] ?User $user
    ): JsonResponse {

        if (!$user) {

            return $this->json([
                'message' => 'Unauthorized'
            ], 401);
        }

        $messages =
            $this->messageService
                ->getConversation(

                    (string) $user->getId(),
                    $userId
                );

        $formattedMessages = [];

        foreach ($messages as $message) {

            $formattedMessages[] = [

                'id' => $message->getId(),

                'senderId' =>
                    $message->getSenderId(),

                'receiverId' =>
                    $message->getReceiverId(),

                'senderEmail' =>
                    $message->getSenderEmail(),

                'receiverEmail' =>
                    $message->getReceiverEmail(),

                'content' =>
                    $message->getContent(),

                'isRead' =>
                    $message->isRead(),

                'createdAt' =>
                    $message
                        ->getCreatedAt()
                        ->format('Y-m-d H:i:s'),
            ];
        }

        return $this->json(
            $formattedMessages
        );
    }
}