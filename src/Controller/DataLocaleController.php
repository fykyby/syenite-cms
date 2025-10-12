<?php

namespace App\Controller;

use App\Entity\DataLocale;
use App\Entity\Settings;
use App\Service\Validation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class DataLocaleController extends AbstractController
{
    #[Route('/__admin/locale', name: 'app_locale')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $settings = $entityManager->getRepository(Settings::class)->find(1);

        $locale = $entityManager->getRepository(DataLocale::class)->findAll();
        $defaultLocaleId = $settings->getDefaultLocale()?->getId();

        return $this->render('data_locale/index.twig', [
            'locales' => $locale,
            'defaultLocaleId' => $defaultLocaleId,
        ]);
    }

    #[Route('/__admin/locale/new', name: 'app_locale_new')]
    public function new(
        Request $request,
        Validation $validation,
        ValidatorInterface $validator,
        EntityManagerInterface $entityManager,
    ): Response {
        $errors = null;
        if ($request->isMethod('POST')) {
            $locale = new DataLocale();
            $locale->setName($request->get('name'));
            $locale->setCode($request->get('code'));
            $locale->setDomain($request->get('domain'));

            $errors = $validation->formatErrors($validator->validate($locale));

            if ($errors === null) {
                $entityManager->persist($locale);
                if ($request->get('default')) {
                    $settings = $entityManager
                        ->getRepository(Settings::class)
                        ->find(1);
                    $settings->setDefaultLocale($locale);
                }
                $entityManager->flush();

                $this->addFlash('success', 'Locale created');

                return $this->redirectToRoute('app_locale_edit', [
                    'id' => $locale->getId(),
                ]);
            } else {
                $this->addFlash('error', 'Validation error(s) occurred');
            }
        }

        return $this->render('data_locale/new.twig', [
            'errors' => $errors,
            'values' => $request->request->all(),
        ]);
    }

    #[
        Route(
            '/__admin/locale/{id}',
            name: 'app_locale_edit',
            requirements: ['id' => '\d+'],
        ),
    ]
    public function edit(
        int $id,
        Request $request,
        Validation $validation,
        ValidatorInterface $validator,
        EntityManagerInterface $entityManager,
    ): Response {
        $locale = $entityManager->getRepository(DataLocale::class)->find($id);
        if ($locale === null) {
            throw new NotFoundHttpException();
        }

        $settings = $entityManager->getRepository(Settings::class)->find(1);

        $isDefault =
            $settings->getDefaultLocale()?->getId() === $locale->getId();
        $errors = null;
        if ($request->isMethod('POST')) {
            $locale->setName($request->get('name'));
            $locale->setCode($request->get('code'));
            $locale->setDomain($request->get('domain'));

            $errors = $validation->formatErrors($validator->validate($locale));

            if ($errors === null) {
                $entityManager->persist($locale);

                if ($request->get('default')) {
                    $settings->setDefaultLocale($locale);
                } elseif ($isDefault) {
                    $settings->setDefaultLocale(null);
                }

                $entityManager->flush();

                $this->addFlash('success', 'Locale created');

                return $this->redirectToRoute('app_locale_edit', [
                    'id' => $locale->getId(),
                ]);
            } else {
                $this->addFlash('error', 'Validation error(s) occurred');
            }
        }

        return $this->render('data_locale/edit.twig', [
            'errors' => $errors,
            'locale' => $locale,
            'default' => $isDefault,
        ]);
    }
}
