<?php

namespace App\Service;

use App\Document\AuditLog;
use Doctrine\ODM\MongoDB\DocumentManager;
use Psr\Log\LoggerInterface;

class AuditLoggerService
{
    public function __construct(
        private DocumentManager $documentManager,
        private LoggerInterface $logger
    ) {
    }

    public function log(
        string $action,
        string $entityType,
        string $entityId,
        string $performedBy,
        array $metadata = []
    ): void {

        try {

            /*
             |------------------------------------------------------------------
             | Création du document AuditLog
             |------------------------------------------------------------------
             */
            $auditLog = new AuditLog();

            $auditLog
                ->setAction($action)
                ->setEntityType($entityType)
                ->setEntityId($entityId)
                ->setPerformedBy($performedBy)
                ->setMetadata(
                    array_merge(
                        $metadata,
                        [
                            'logged_at' => (new \DateTime())->format('Y-m-d H:i:s'),
                            'environment' => $_ENV['APP_ENV'] ?? 'unknown',
                        ]
                    )
                );

            /*
             |------------------------------------------------------------------
             | Sauvegarde MongoDB
             |------------------------------------------------------------------
             */
            $this->documentManager->persist($auditLog);
            $this->documentManager->flush();

        } catch (\Throwable $exception) {

            /*
             |------------------------------------------------------------------
             | Sécurité : ne jamais casser l'application
             |------------------------------------------------------------------
             */
            $this->logger->error(
                'MongoDB AuditLog failure',
                [
                    'action' => $action,
                    'entityType' => $entityType,
                    'entityId' => $entityId,
                    'performedBy' => $performedBy,
                    'metadata' => $metadata,
                    'error' => $exception->getMessage(),
                ]
            );
        }
    }
}