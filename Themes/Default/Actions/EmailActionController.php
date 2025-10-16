<?php

declare(strict_types=1);

namespace Themes\Default\Actions;

use App\Service\MailerService;
use App\Service\Validation;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class EmailActionController extends AbstractController
{
    public function sendContact(
        Request $request,
        Validation $validation,
        MailerService $mailer,
    ): Response {
        $data = $request->request->all();
        $rules = [
            'name' => 'required',
            'email' => 'required|email',
            'message' => 'required',
        ];

        $errors = $validation->validate($data, $rules);
        if (!empty($errors)) {
            return $this->json([
                'errors' => $errors,
            ])->setStatusCode(400);
        }

        $subject = "Contact - {$request->getHost()} - {$data['email']}";
        $mailer->sendClientEmail(null, $subject, $data);

        return $this->json([
            'errors' => null,
        ]);
    }
}
