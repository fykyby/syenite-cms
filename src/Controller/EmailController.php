<?php

namespace App\Controller;

use App\Entity\Settings;
use App\Service\Cms;
use App\Service\Validation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;

final class EmailController extends AbstractController
{
    #[
        Route(
            '/__emails/{name}',
            name: 'app_email',
            requirements: ['name' => '\w+'],
            methods: ['POST'],
        ),
    ]
    public function send(
        string $name,
        Cms $cms,
        Request $request,
        Validation $validation,
        EntityManagerInterface $entityManager,
    ): Response {
        $data = $request->request->all();
        $rules = $cms->getConfig()['emails'][$name];
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
        $email->subject("{$name} - {$request->getHost()} - {$data['email']}");
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
