<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Settings;
use App\Service\Validation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class SettingsController extends AbstractController
{
    #[Route('/__admin/settings', name: 'app_settings')]
    public function index(
        Request $request,
        EntityManagerInterface $entityManager,
        Validation $validation,
        ValidatorInterface $validator,
    ): Response {
        $settings = $entityManager->getRepository(Settings::class)->find(1);

        $errors = null;
        if ($request->isMethod('POST')) {
            $data = $request->request->all();
            $settings->setEmailAccountUsername($data['email_account_username']);
            $settings->setEmailAccountPassword($data['email_account_password']);
            $settings->setEmailAccountHost($data['email_account_host']);
            $settings->setEmailAccountPort($data['email_account_port']);

            $errors = $validation->formatErrors(
                $validator->validate($settings),
            );

            if ($errors === null) {
                $entityManager->persist($settings);
                $entityManager->flush();

                $this->addFlash('success', 'Settings saved');

                return $this->redirectToRoute('app_settings');
            } else {
                $this->addFlash('error', 'Validation error(s) occurred');
            }
        }

        return $this->render('settings/index.twig', [
            'values' => $settings,
            'errors' => $errors,
        ]);
    }
}
