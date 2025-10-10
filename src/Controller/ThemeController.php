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
    #[Route('/__admin/theme', name: 'app_theme')]
    public function edit(Cms $cms): Response
    {
        $layouts = $cms->listLayouts();

        return $this->render('theme/index.twig', [
            'layouts' => $layouts,
        ]);
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

                $this->addFlash('error', 'Validation error(s) occurred');
            } else {
                $settings->setCurrentTheme($targetTheme);
                $entityManager->flush();

                $this->addFlash('success', 'Theme changed');

                return $this->redirectToRoute('app_theme');
            }
        }

        return $this->render('theme/change.twig', [
            'themes' => $themes,
            'currentTheme' => $currentTheme,
            'error' => $error,
        ]);
    }

    #[
        Route(
            '/__admin/theme/layouts/{name}',
            name: 'app_theme_layout',
            requirements: ['name' => '\w+'],
        ),
    ]
    public function layout(
        string $name,
        Cms $cms,
        Request $request,
        DataTransformer $dataTransformer,
        EntityManagerInterface $entityManager,
        Validation $validation,
    ): Response {
        $settings = $entityManager->getRepository(Settings::class)->find(1);

        $schema = $cms->getLayoutSchema($name);
        if ($schema === null) {
            throw new NotFoundHttpException();
        }

        $data = [];
        $hasErrors = false;
        if ($request->isMethod('POST')) {
            $requestData = $request->request->all()['layout'];

            $built = $dataTransformer->buildValidationDataAndRules(
                $schema['fields'],
                $requestData,
            );

            $validationData = $built['data'];
            $validationRules = $built['rules'];

            $errors =
                $validation->validate($validationData, $validationRules) ?? [];
            $hasErrors = $hasErrors || !empty($errors);

            $data = $dataTransformer->attachValuesAndErrors(
                $schema['fields'],
                $requestData,
                $errors,
            );

            $settings->setLayoutData($requestData);
            if (!$hasErrors) {
                $entityManager->flush();

                $this->addFlash('success', 'Layout saved');

                return $this->redirectToRoute('app_theme_layout', [
                    'name' => $name,
                ]);
            } else {
                $this->addFlash('error', 'Validation error(s) occurred');
            }
        } else {
            $data = $dataTransformer->attachValuesAndErrors(
                $schema['fields'],
                $settings->getLayoutData(),
                [],
            );
        }

        return $this->render('theme/layout.twig', [
            'name' => $name,
            'data' => $data,
        ]);
    }
}
