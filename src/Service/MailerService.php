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

    public function sendClientEmail(string $subject, array $data): void
    {
        $account = $this->settingsManager->getValue('email_account');

        $html = $this->twig->render('client/_email.twig', [
            'data' => $data,
        ]);

        $email = new Email();
        if ($data['email']) {
            $email->replyTo($data['email']);
        } else {
            $data['email'] = '';
        }
        $email->from($account['username']);
        $email->to($account['username']);
        $email->subject($subject);
        $email->html($html);

        $username = urlencode($account['username']);
        $password = urlencode($account['password']);
        $host = urlencode($account['host']);
        $port = urlencode($account['port']);

        $dsn = "smtp://{$username}:{$password}@{$host}:{$port}";
        $transport = Transport::fromDsn($dsn);
        $mailer = new Mailer($transport);
        $mailer->send($email);
    }
}
