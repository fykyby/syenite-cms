<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\Cms;
use App\Service\SettingsManager;
use App\Service\Validation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Cache\CacheInterface;

final class SettingsController extends AbstractController
{
    #[
        Route(
            '/__admin/settings/email-account',
            name: 'app_settings_email_account',
        ),
    ]
    public function emailAccount(
        Request $request,
        Validation $validation,
        SettingsManager $settingsManager,
    ): Response {
        $data = $settingsManager->getValue('emailAccount');

        $errors = null;
        if ($request->isMethod('POST')) {
            $data = $request->request->all();

            $errors = $validation->validate($data, [
                'username' => 'email',
            ]);

            if ($errors === null) {
                $settingsManager->setValue('emailAccount', $data);

                $this->addFlash('success', 'Settings saved');

                return $this->redirectToRoute('app_settings');
            } else {
                $this->addFlash('error', 'Validation error(s) occurred');
            }
        }

        return $this->render('settings/email_account.twig', [
            'values' => $data,
            'errors' => $errors,
        ]);
    }

    #[Route('/__admin/settings/theme', name: 'app_settings_theme')]
    public function change(
        Cms $cms,
        Request $request,
        EntityManagerInterface $entityManager,
        CacheInterface $cache,
        SettingsManager $settingsManager,
    ): Response {
        $themes = $cms->listThemes();
        $currentTheme = $cms->getThemeName();

        $error = null;
        if ($request->isMethod('POST')) {
            $targetTheme = $request->request->get('theme');

            if (!in_array($targetTheme, $themes)) {
                $error = 'Invalid theme';

                $this->addFlash('error', 'Validation error(s) occurred');
            } else {
                $settingsManager->setValue(
                    SettingsManager::$currentThemeKey,
                    $targetTheme,
                );

                $entityManager->flush();
                $cache->delete('app.settings.theme');

                $this->addFlash('success', 'Theme changed');

                return $this->redirectToRoute('app_theme_edit');
            }
        }

        return $this->render('settings/theme.twig', [
            'themes' => $themes,
            'currentTheme' => $currentTheme,
            'error' => $error,
        ]);
    }
}
