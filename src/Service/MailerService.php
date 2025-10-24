<?php

declare(strict_types=1);

namespace App\Service;

use Twig\Environment;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;

class MailerService
{
    public function __construct(
        private SettingsManager $settingsManager,
        private Environment $twig,
        private MailerInterface $mailer,
    ) {}

    public function sendClientEmail(
        ?string $receipient,
        string $subject,
        array $data,
    ): void {
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
        $email->to($receipient ?? $account['username']);
        $email->subject($subject);
        $email->html($html);

        $username = urlencode($account['username']);
        $password = urlencode($account['password']);

        $dsn = "smtp://{$username}:{$password}@{$account['host']}:{$account['port']}";
        $transport = Transport::fromDsn($dsn);
        $mailer = new Mailer($transport);
        $mailer->send($email);
    }

    public function sendPasswordResetEmail(string $email, string $url): void
    {
        $sender = explode(
            ':',
            str_replace('smtp://', '', $_ENV['MAILER_DSN']),
        )[0];

        $email = (new Email())
            ->from($sender)
            ->to($email)
            ->subject('CMS Password Reset')
            ->text($url);

        $this->mailer->send($email);
    }
}
