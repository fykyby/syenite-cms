<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\Cms;
use App\Service\SettingsManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Cache\CacheInterface;

final class ThemeController extends AbstractController
{
    #[Route('/__admin/theme', name: 'app_theme_edit')]
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
                $settingsManager->setValue('current_theme', $targetTheme);

                $entityManager->flush();
                $cache->delete('app.settings.theme');

                $this->addFlash('success', 'Theme changed');

                return $this->redirectToRoute('app_theme_edit');
            }
        }

        return $this->render('theme/edit.twig', [
            'themes' => $themes,
            'currentTheme' => $currentTheme,
            'error' => $error,
        ]);
    }
}
