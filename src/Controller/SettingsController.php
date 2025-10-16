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
        $settings = $settingsManager->get();

        $errors = null;
        if ($request->isMethod('POST')) {
            $data = $request->request->all();
            $settings = array_merge($settings, $request->request->all());

            $errors = $validation->validate($data, [
                'email_account.username' => 'email',
            ]);

            if ($errors === null) {
                $settingsManager->set($settings);

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
