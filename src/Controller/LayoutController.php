<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\DataLocale;
use App\Entity\LayoutData;
use App\Service\Cms;
use App\Service\DataTransformer;
use App\Service\Validation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class LayoutController extends AbstractController
{
    #[Route('/__admin/layouts', name: 'app_layout')]
    public function index(Cms $cms): Response
    {
        $layouts = $cms->listLayouts();

        return $this->render('layout/index.twig', [
            'layouts' => $layouts,
        ]);
    }

    #[
        Route(
            '/__admin/layouts/{name}',
            name: 'app_layout_edit',
            requirements: ['name' => '\w+'],
        ),
    ]
    public function edit(
        string $name,
        Cms $cms,
        Request $request,
        DataTransformer $dataTransformer,
        EntityManagerInterface $entityManager,
        Validation $validation,
    ): Response {
        $localeId = $request
            ->getSession()
            ->get(DataLocaleController::SESSION_LOCALE_ID_KEY);
        $layoutData = $entityManager
            ->getRepository(LayoutData::class)
            ->findOneBy([
                'name' => $name,
                'theme' => $cms->getThemeName(),
                'locale' => $localeId,
            ]);

        if ($layoutData === null) {
            $layoutData = new LayoutData();
            $layoutData->setName($name);
            $layoutData->setTheme($cms->getThemeName());
            $layoutData->setLocale(
                $entityManager
                    ->getRepository(DataLocale::class)
                    ->find($localeId),
            );
            $layoutData->setData([]);
            $entityManager->persist($layoutData);
        }

        $schema = $cms->getLayoutSchema($name);
        if ($schema === null) {
            throw $this->createNotFoundException();
        }

        $data = [];
        $hasErrors = false;
        if ($request->isMethod('POST')) {
            $requestData = $request->request->all()['layout'] ?? [];

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

            $layoutData->setData($requestData);
            if (!$hasErrors) {
                $entityManager->flush();

                $this->addFlash('success', 'Layout saved');

                return $this->redirectToRoute('app_layout_edit', [
                    'name' => $name,
                ]);
            } else {
                $this->addFlash('error', 'Validation error(s) occurred');
            }
        } else {
            $data = $dataTransformer->attachValuesAndErrors(
                $schema['fields'],
                $layoutData->getData(),
                [],
            );
        }

        return $this->render('layout/edit.twig', [
            'name' => $name,
            'data' => $data,
        ]);
    }
}
