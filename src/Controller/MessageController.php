<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Service\MessageService;
use App\Service\NotificationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/messages')]
class MessageController extends AbstractController
{
    #[Route('/', name: 'app_messages')]
    public function index(
        MessageService $messageService,
        UserRepository $userRepository
    ): Response {

        $this->denyAccessUnlessGranted('ROLE_USER');

        $user = $this->getUser();

        $inbox = $messageService->getInbox(
            (string) $user->getId()
        );

        /*
         |------------------------------------------------------------------
         | Liste utilisateurs potentiels pour nouvelles conversations
         |------------------------------------------------------------------
         */
        $users = array_filter(
            $userRepository->findAll(),
            fn($u) => $u->getId() !== $user->getId()
        );

        return $this->render(
            'message/index.html.twig',
            [
                'inbox' => $inbox,
                'users' => $users,
                'currentUser' => $user,
            ]
        );
    }

    #[Route('/conversation/{id}', name: 'app_message_conversation')]
    public function conversation(
        int $id,
        Request $request,
        UserRepository $userRepository,
        MessageService $messageService,
        NotificationService $notificationService
    ): Response {

        $this->denyAccessUnlessGranted('ROLE_USER');

        $currentUser = $this->getUser();

        $otherUser = $userRepository->find($id);

        if (!$otherUser) {
            throw $this->createNotFoundException(
                'Utilisateur introuvable.'
            );
        }

        /*
         |------------------------------------------------------------------
         | Envoi message
         |------------------------------------------------------------------
         */
        if ($request->isMethod('POST')) {

            $content = trim(
                $request->request->get('content', '')
            );

            if (!empty($content)) {

                $messageService->sendMessage(
                    (string) $currentUser->getId(),
                    (string) $otherUser->getId(),
                    $currentUser->getEmail(),
                    $otherUser->getEmail(),
                    $content
                );

                /*
                 |----------------------------------------------------------
                 | Notification MongoDB
                 |----------------------------------------------------------
                 */
                $notificationService->createNotification(
                    (string) $otherUser->getId(),
                    'Nouveau message reçu',
                    sprintf(
                        '%s %s vous a envoyé un message.',
                        $currentUser->getPrenom(),
                        $currentUser->getNom()
                    ),
                    'message'
                );

                $this->addFlash(
                    'success',
                    'Message envoyé avec succès.'
                );

                return $this->redirectToRoute(
                    'app_message_conversation',
                    [
                        'id' => $otherUser->getId(),
                    ]
                );
            }
        }

        /*
         |------------------------------------------------------------------
         | Marquer messages comme lus
         |------------------------------------------------------------------
         */
        $messageService->markConversationAsRead(
            (string) $currentUser->getId(),
            (string) $otherUser->getId()
        );

        /*
         |------------------------------------------------------------------
         | Conversation
         |------------------------------------------------------------------
         */
        $conversation = $messageService->getConversation(
            (string) $currentUser->getId(),
            (string) $otherUser->getId()
        );

        return $this->render(
            'message/conversation.html.twig',
            [
                'conversation' => $conversation,
                'recipient' => $otherUser,
                'otherUser' => $otherUser,
                'currentUser' => $currentUser,
            ]
        );
    }
}