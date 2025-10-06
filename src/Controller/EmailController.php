<?php

namespace App\Controller;

use App\Service\Cms;
use App\Service\Validation;
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
    ): Response {
        $data = $request->request->all();
        $rules = $cms->getConfig()['emails'][$name];
        // TODO: get from db
        $settings = [
            'username' => 'user',
            'password' => 'pass',
            'host' => 'smtp.example.com',
            'port' => 25,
        ];

        $errors = $validation->validate($data, $rules);
        if (!empty($errors)) {
            return $this->json([
                'errors' => $errors,
            ])->setStatusCode(400);
        }

        // TODO: send email

        $dsn = "smtp://{$settings['username']}:{$settings['password']}@{$settings['host']}:{$settings['port']}";
        $transport = Transport::fromDsn($dsn);
        $mailer = new Mailer($transport);

        $email = new Email();
        $email->from($settings['username']);
        $email->to($settings['username']);
        $email->subject('todo');
        // $email->replyTo($data['email']);

        // $mailer->send();

        return $this->json([
            'errors' => null,
        ]);
    }
}
