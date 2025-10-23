<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\DataLocale;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PasswordResetService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private MailerInterface $mailer,
        private UrlGeneratorInterface $urlGenerator,
    ) {}

    public function sendResetEmail(User $user): void
    {
        $expires = time() + 900;
        $signature = $this->getSignature($user->getId(), $expires);

        $defaultLocale = $this->entityManager
            ->getRepository(DataLocale::class)
            ->findOneBy(['isDefault' => true]);
        $domain = $defaultLocale->getDomain() ?? 'localhost';

        $path = $this->urlGenerator->generate(
            'app_auth_password_reset_target',
            [
                'signature' => $signature,
                'userId' => $user->getId(),
                'expires' => $expires,
            ],
        );

        $url = "https://{$domain}{$path}";

        $sender = explode(':', ltrim($_ENV['MAILER_DSN'], 'smtp://'))[0];

        $email = (new Email())
            ->from($sender)
            ->to($user->getEmail())
            ->subject("Password Reset: {$domain} - kreator")
            ->text($url);

        $this->mailer->send($email);
    }

    public function getSignature($userId, $expires): string
    {
        return hash_hmac('sha256', "{$userId}|{$expires}", $_ENV['APP_SECRET']);
    }
}
