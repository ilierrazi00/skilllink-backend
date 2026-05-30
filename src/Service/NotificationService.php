<?php

namespace App\Service;

use App\Document\Notification;
use Doctrine\ODM\MongoDB\DocumentManager;

class NotificationService
{
    public function __construct(
        private readonly DocumentManager $documentManager
    ) {
    }

    /*
     |------------------------------------------------------------------
     | Créer une notification
     |------------------------------------------------------------------
     */
    public function createNotification(
        string $userId,
        string $type,
        string $title,
        string $message,
        array $metadata = []
    ): Notification {

        $notification = new Notification();

        $notification
            ->setUserId($userId)
            ->setType($type)
            ->setTitle($title)
            ->setMessage($message)
            ->setMetadata($metadata);

        $this->documentManager->persist($notification);
        $this->documentManager->flush();

        return $notification;
    }

    /*
     |------------------------------------------------------------------
     | Récupérer notifications utilisateur
     |------------------------------------------------------------------
     */
    public function getUserNotifications(
        string $userId,
        int $limit = 20
    ): array {

        return $this->documentManager
            ->getRepository(Notification::class)
            ->findBy(
                ['userId' => $userId],
                ['createdAt' => 'DESC'],
                $limit
            );
    }

    /*
     |------------------------------------------------------------------
     | Compter notifications non lues
     |------------------------------------------------------------------
     */
    public function countUnreadNotifications(
        string $userId
    ): int {

        return $this->documentManager
            ->getRepository(Notification::class)
            ->count([
                'userId' => $userId,
                'isRead' => false,
            ]);
    }

    /*
     |------------------------------------------------------------------
     | Marquer une notification comme lue
     |------------------------------------------------------------------
     */
    public function markAsRead(
        string $notificationId
    ): void {

        $notification = $this->documentManager
            ->getRepository(Notification::class)
            ->find($notificationId);

        if (!$notification) {
            return;
        }

        $notification->markAsRead();

        $this->documentManager->flush();
    }

    /*
     |------------------------------------------------------------------
     | Marquer toutes comme lues
     |------------------------------------------------------------------
     */
    public function markAllAsRead(
        string $userId
    ): void {

        $notifications = $this->documentManager
            ->getRepository(Notification::class)
            ->findBy([
                'userId' => $userId,
                'isRead' => false,
            ]);

        foreach ($notifications as $notification) {
            $notification->markAsRead();
        }

        $this->documentManager->flush();
    }
}