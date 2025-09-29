<?php

namespace App\Controller;

use App\Service\Cms;
use App\Service\Validation;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
    public function index(
        string $name,
        Cms $cms,
        Request $request,
        Validation $validation,
    ): Response {
        $data = $request->request->all();
        $rules = $cms->getConfig()['emails'][$name];

        $errors = $validation->validate($data, $rules);
        if (!empty($errors)) {
            return $this->json([
                'errors' => $errors,
            ])->setStatusCode(400);
        }

        // TODO: send email

        return $this->json([
            'errors' => null,
        ]);
    }
}
