<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\DataLocale;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PasswordResetService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private MailerService $mailerService,
        private UrlGeneratorInterface $urlGenerator,
    ) {}

    public function sendResetEmail(User $user): void
    {
        $expires = time() + 6;
        $signature = $this->getSignature($user->getId(), $expires);

        $path = $this->urlGenerator->generate(
            'app_auth_password_reset_target',
            [
                'signature' => $signature,
                'userId' => $user->getId(),
                'expires' => $expires,
            ],
        );

        $url = '';

        $defaultLocale = $this->entityManager
            ->getRepository(DataLocale::class)
            ->findOneBy(['isDefault' => true]);

        if ($defaultLocale === null) {
            throw new \Exception('Default locale not found');
        }

        $url = $defaultLocale->getDomain()
            ? "https://{$defaultLocale->getDomain()}{$path}"
            : "http://localhost:8000{$path}";

        $this->mailerService->sendPasswordResetEmail($user->getEmail(), $url);
    }

    public function getSignature($userId, $expires): string
    {
        return hash_hmac('sha256', "{$userId}|{$expires}", $_ENV['APP_SECRET']);
    }
}
