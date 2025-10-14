<?php

namespace App\EventListener;

use App\Entity\DataLocale;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

final class LoginListener
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {}

    #[AsEventListener]
    public function onLoginSuccessEvent(LoginSuccessEvent $event): void
    {
        $defaultLocale = $this->entityManager
            ->getRepository(DataLocale::class)
            ->findOneBy(['isDefault' => true]);

        $event
            ->getRequest()
            ->getSession()
            ->set('__locale', $defaultLocale->getId());
    }
}
