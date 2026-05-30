<?php

namespace App\Repository;

use App\Entity\ResetPasswordRequest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use SymfonyCasts\Bundle\ResetPassword\Model\ResetPasswordRequestInterface;
use SymfonyCasts\Bundle\ResetPassword\Persistence\ResetPasswordRequestRepositoryInterface;

class ResetPasswordRequestRepository extends ServiceEntityRepository implements ResetPasswordRequestRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ResetPasswordRequest::class);
    }

    public function createResetPasswordRequest(
        object $user,
        \DateTimeInterface $expiresAt,
        string $selector,
        string $hashedToken
    ): ResetPasswordRequestInterface {
        return new ResetPasswordRequest(
            $user,
            $expiresAt,
            $selector,
            $hashedToken
        );
    }

    public function persistResetPasswordRequest(
        ResetPasswordRequestInterface $resetPasswordRequest
    ): void {
        $this->getEntityManager()->persist($resetPasswordRequest);
        $this->getEntityManager()->flush();
    }

    public function findResetPasswordRequest(
        string $selector
    ): ?ResetPasswordRequestInterface {
        return $this->findOneBy([
            'selector' => $selector,
        ]);
    }

    public function getMostRecentNonExpiredRequestDate(
        object $user
    ): ?\DateTimeInterface {
        $resetRequest = $this->createQueryBuilder('r')
            ->andWhere('r.user = :user')
            ->andWhere('r.expiresAt > :now')
            ->setParameter('user', $user)
            ->setParameter('now', new \DateTimeImmutable())
            ->orderBy('r.requestedAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $resetRequest?->getRequestedAt();
    }

    public function removeResetPasswordRequest(
        ResetPasswordRequestInterface $resetPasswordRequest
    ): void {
        $this->getEntityManager()->remove($resetPasswordRequest);
        $this->getEntityManager()->flush();
    }

    public function removeExpiredResetPasswordRequests(): int
    {
        return $this->createQueryBuilder('r')
            ->delete()
            ->andWhere('r.expiresAt <= :now')
            ->setParameter('now', new \DateTimeImmutable())
            ->getQuery()
            ->execute();
    }

    public function getUserIdentifier(object $user): string
    {
        return $user->getUserIdentifier();
    }
}