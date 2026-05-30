<?php

namespace App\Controller;

use App\Document\Notification;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/notifications')]
class NotificationController extends AbstractController
{
    #[Route('/', name: 'app_notifications', methods: ['GET'])]
    public function index(
        DocumentManager $documentManager,
        Security $security
    ): Response {

        /*
         |------------------------------------------------------------------
         | Sécurité utilisateur connecté
         |------------------------------------------------------------------
         */
        $user = $security->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        /*
         |------------------------------------------------------------------
         | Récupération notifications MongoDB
         |------------------------------------------------------------------
         */
        $notifications = $documentManager
            ->getRepository(Notification::class)
            ->findBy(
                [
                    'userId' => (string) $user->getId(),
                ],
                [
                    'createdAt' => 'DESC',
                ]
            );

        /*
         |------------------------------------------------------------------
         | Compteurs
         |------------------------------------------------------------------
         */
        $unreadCount = count(
            array_filter(
                $notifications,
                fn(Notification $notification) =>
                    !$notification->isRead()
            )
        );

        return $this->render(
            'notification/index.html.twig',
            [
                'notifications' => $notifications,
                'unreadCount' => $unreadCount,
                'user' => $user,
            ]
        );
    }

    #[Route('/{id}/read', name: 'app_notification_read', methods: ['POST'])]
    public function markAsRead(
        string $id,
        DocumentManager $documentManager,
        Security $security
    ): Response {

        /*
         |------------------------------------------------------------------
         | Sécurité utilisateur connecté
         |------------------------------------------------------------------
         */
        $user = $security->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        /*
         |------------------------------------------------------------------
         | Recherche notification
         |------------------------------------------------------------------
         */
        $notification = $documentManager
            ->getRepository(Notification::class)
            ->find($id);

        if (
            !$notification
            || $notification->getUserId() !== (string) $user->getId()
        ) {
            throw $this->createAccessDeniedException(
                'Notification non autorisée.'
            );
        }

        /*
         |------------------------------------------------------------------
         | Mise à jour lecture
         |------------------------------------------------------------------
         */
        $notification->setIsRead(true);

        $documentManager->persist($notification);
        $documentManager->flush();

        $this->addFlash(
            'success',
            'Notification marquée comme lue.'
        );

        return $this->redirectToRoute(
            'app_notifications'
        );
    }

    #[Route('/mark-all-read', name: 'app_notifications_mark_all_read', methods: ['POST'])]
    public function markAllAsRead(
        DocumentManager $documentManager,
        Security $security
    ): Response {

        /*
         |------------------------------------------------------------------
         | Sécurité utilisateur connecté
         |------------------------------------------------------------------
         */
        $user = $security->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        /*
         |------------------------------------------------------------------
         | Toutes les notifications utilisateur
         |------------------------------------------------------------------
         */
        $notifications = $documentManager
            ->getRepository(Notification::class)
            ->findBy([
                'userId' => (string) $user->getId(),
                'isRead' => false,
            ]);

        foreach ($notifications as $notification) {
            $notification->setIsRead(true);
            $documentManager->persist($notification);
        }

        $documentManager->flush();

        $this->addFlash(
            'success',
            'Toutes les notifications ont été marquées comme lues.'
        );

        return $this->redirectToRoute(
            'app_notifications'
        );
    }
}