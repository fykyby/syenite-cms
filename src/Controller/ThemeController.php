<?php

namespace App\Controller;

use App\Entity\Settings;
use App\Service\Cms;
use App\Service\DataTransformer;
use App\Service\Validation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

final class ThemeController extends AbstractController
{
    #[Route('/__admin/theme', name: 'app_theme_edit')]
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

                $this->addFlash('error', 'Validation error(s) occurred');
            } else {
                $settings->setCurrentTheme($targetTheme);
                $entityManager->flush();

                $this->addFlash('success', 'Theme changed');

                return $this->redirectToRoute('app_theme');
            }
        }

        return $this->render('theme/edit.twig', [
            'themes' => $themes,
            'currentTheme' => $currentTheme,
            'error' => $error,
        ]);
    }
}
