<?php

declare(strict_types=1);

namespace Themes\Default\Actions;

use App\Entity\Settings;
use App\Service\Validation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;

final class EmailActionController extends AbstractController
{
    public function sendContact(
        Request $request,
        EntityManagerInterface $entityManager,
        Validation $validation,
    ): Response {
        $data = $request->request->all();
        $rules = [
            'name' => 'required',
            'email' => 'required|email',
            'message' => 'required',
        ];

        $settings = $entityManager
            ->getRepository(Settings::class)
            ->find(1)
            ->getEmailSettings();

        $errors = $validation->validate($data, $rules);
        if (!empty($errors)) {
            return $this->json([
                'errors' => $errors,
            ])->setStatusCode(400);
        }

        $html = $this->render('client/_email.twig', [
            'data' => $data,
        ]);

        $email = new Email();
        if ($data['email']) {
            $email->replyTo($data['email']);
        } else {
            $data['email'] = '';
        }
        $email->from($settings['username']);
        $email->to($settings['username']);
        $email->subject("Contact - {$request->getHost()} - {$data['email']}");
        $email->html($html->getContent());

        $username = urlencode($settings['username']);
        $password = urlencode($settings['password']);
        $host = urlencode($settings['host']);
        $port = urlencode($settings['port']);

        $dsn = "smtp://{$username}:{$password}@{$host}:{$port}";
        $transport = Transport::fromDsn($dsn);
        $mailer = new Mailer($transport);
        $mailer->send($email);

        return $this->json([
            'errors' => null,
        ]);
    }
}
