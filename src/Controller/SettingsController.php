<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\SettingsManager;
use App\Service\Validation;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SettingsController extends AbstractController
{
    #[Route('/__admin/settings', name: 'app_settings')]
    public function index(
        Request $request,
        Validation $validation,
        SettingsManager $settingsManager,
    ): Response {
        $emailAccount = $settingsManager->getValue('emailAccount');

        $errors = null;
        if ($request->isMethod('POST')) {
            $data = $request->request->all();
            $emailAccount = $request->request->all()['email_account'];

            $errors = $validation->validate($data, [
                'email_account.username' => 'email',
            ]);

            if ($errors === null) {
                $settingsManager->setValue('emailAccount', $emailAccount);

                $this->addFlash('success', 'Settings saved');

                return $this->redirectToRoute('app_settings');
            } else {
                $this->addFlash('error', 'Validation error(s) occurred');
            }
        }

        return $this->render('settings/index.twig', [
            'values' => $emailAccount,
            'errors' => $errors,
        ]);
    }
}
