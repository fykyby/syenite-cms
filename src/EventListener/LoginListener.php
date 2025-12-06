<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Controller\DataLocaleController;
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
            ->set(
                DataLocaleController::SESSION_LOCALE_ID_KEY,
                $defaultLocale->getId(),
            );
    }
}
