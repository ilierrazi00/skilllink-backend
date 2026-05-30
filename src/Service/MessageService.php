<?php

namespace App\Service;

use App\Document\Message;
use Doctrine\ODM\MongoDB\DocumentManager;

class MessageService
{
    public function __construct(
        private DocumentManager $documentManager
    ) {}

    /*
     |----------------------------------------------------------------------
     | Envoyer un message
     |----------------------------------------------------------------------
     */
    public function sendMessage(
        string $senderId,
        string $receiverId,
        string $senderEmail,
        string $receiverEmail,
        string $content
    ): void {

        $message = new Message();

        $message
            ->setSenderId($senderId)
            ->setReceiverId($receiverId)
            ->setSenderEmail($senderEmail)
            ->setReceiverEmail($receiverEmail)
            ->setContent($content);

        $this->documentManager->persist($message);
        $this->documentManager->flush();
    }

    /*
     |----------------------------------------------------------------------
     | Récupérer conversation entre deux utilisateurs
     |----------------------------------------------------------------------
     */
    public function getConversation(
        string $userId,
        string $otherUserId
    ): array {

        return $this->documentManager
            ->getRepository(Message::class)
            ->createQueryBuilder()
            ->addOr(
                $this->documentManager
                    ->getRepository(Message::class)
                    ->createQueryBuilder()
                    ->field('senderId')->equals($userId)
                    ->field('receiverId')->equals($otherUserId)
                    ->getQueryArray()
            )
            ->addOr(
                $this->documentManager
                    ->getRepository(Message::class)
                    ->createQueryBuilder()
                    ->field('senderId')->equals($otherUserId)
                    ->field('receiverId')->equals($userId)
                    ->getQueryArray()
            )
            ->sort('createdAt', 'ASC')
            ->getQuery()
            ->execute()
            ->toArray();
    }

    /*
     |----------------------------------------------------------------------
     | Inbox utilisateur
     |----------------------------------------------------------------------
     */
    public function getInbox(
        string $userId
    ): array {

        return $this->documentManager
            ->getRepository(Message::class)
            ->findBy(
                ['receiverId' => $userId],
                ['createdAt' => 'DESC']
            );
    }

    /*
     |----------------------------------------------------------------------
     | Messages non lus
     |----------------------------------------------------------------------
     */
    public function countUnreadMessages(
        string $userId
    ): int {

        return $this->documentManager
            ->getRepository(Message::class)
            ->createQueryBuilder()
            ->field('receiverId')->equals($userId)
            ->field('isRead')->equals(false)
            ->getQuery()
            ->count();
    }

    /*
     |----------------------------------------------------------------------
     | Marquer conversation comme lue
     |----------------------------------------------------------------------
     */
    public function markConversationAsRead(
        string $userId,
        string $otherUserId
    ): void {

        $messages = $this->documentManager
            ->getRepository(Message::class)
            ->findBy([
                'senderId' => $otherUserId,
                'receiverId' => $userId,
                'isRead' => false,
            ]);

        foreach ($messages as $message) {
            $message->markAsRead();
            $this->documentManager->persist($message);
        }

        $this->documentManager->flush();
    }
}