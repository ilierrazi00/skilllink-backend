<?php

namespace App\EventSubscriber;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\Application;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ApplicationSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private Security $security
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => [
                ['setCandidateProfile', EventPriorities::PRE_WRITE],
            ],
        ];
    }

    public function setCandidateProfile(
        ViewEvent $event
    ): void {

        $application = $event->getControllerResult();

        if (
            !$application instanceof Application
            || $event->getRequest()->getMethod() !== 'POST'
        ) {
            return;
        }

        $user = $this->security->getUser();

        if (
            !$user
            || !$user->getCandidateProfile()
        ) {
            return;
        }

        $application->setCandidateProfile(
            $user->getCandidateProfile()
        );
    }
}