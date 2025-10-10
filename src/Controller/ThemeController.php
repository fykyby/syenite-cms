<?php

namespace App\Controller;

use App\Entity\Settings;
use App\Service\Cms;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ThemeController extends AbstractController
{
    #[Route('/__admin/theme', name: 'app_theme')]
    public function index(Cms $cms): Response
    {
        $layoutName = 'Base';
        $schema = $cms->getLayoutSchema($layoutName);

        return $this->render('theme/index.html.twig', []);
    }

    #[Route('/__admin/theme/change', name: 'app_theme_change')]
    public function change(
        Cms $cms,
        Request $request,
        EntityManagerInterface $entityManager,
    ): Response {
        $themes = $cms->listThemes();
        $currentTheme = $cms->getThemeName();

        $error = null;
        if ($request->isMethod('POST')) {
            $settings = $entityManager->getRepository(Settings::class)->find(1);
            $targetTheme = $request->request->get('theme');

            if (!in_array($targetTheme, $themes)) {
                $error = 'Invalid theme';
            } else {
                $settings->setCurrentTheme($targetTheme);
                $entityManager->flush();

                $this->addFlash('success', 'Theme changed');

                return $this->redirectToRoute('app_theme');
            }
        }

        return $this->render('theme/change.html.twig', [
            'themes' => $themes,
            'currentTheme' => $currentTheme,
            'error' => $error,
        ]);
    }
}
