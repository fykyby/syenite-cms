<?php

declare(strict_types=1);

namespace App\Service;

use Twig\Environment;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;

class MailerService
{
    public function __construct(
        private SettingsManager $settingsManager,
        private Environment $twig,
    ) {}

    private function getAccountConfig(): array
    {
        return $this->settingsManager->getValue('emailAccount');
    }

    private function getDsn(): string
    {
        $config = $this->getAccountConfig();

        $username = urlencode($config['username']);
        $password = urlencode($config['password']);
        $host = urlencode($config['host']);
        $port = urlencode($config['port']);

        return "smtp://{$username}:{$password}@{$host}:{$port}";
    }

    private function getMailer(): Mailer
    {
        $dsn = $this->getDsn();
        $transport = Transport::fromDsn($dsn);
        return new Mailer($transport);
    }

    public function sendClientEmail(
        ?string $receipient,
        string $subject,
        array $data,
    ): void {
        $html = $this->twig->render('client/_email.twig', [
            'data' => $data,
        ]);

        $config = $this->getAccountConfig();

        $email = (new Email())
            ->from($config['username'])
            ->to($receipient ?? $config['username'])
            ->subject($subject)
            ->html($html);

        if ($data['email']) {
            $email->replyTo($data['email']);
        }

        $mailer = $this->getMailer();
        $mailer->send($email);
    }

    public function sendPasswordResetEmail(string $email, string $url): void
    {
        $config = $this->getAccountConfig();

        $email = (new Email())
            ->from($config['username'])
            ->to($email)
            ->subject('CMS Password Reset')
            ->text($url);

        $mailer = $this->getMailer();
        $mailer->send($email);
    }
}
