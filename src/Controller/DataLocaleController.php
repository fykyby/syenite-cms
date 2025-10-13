<?php

namespace App\Controller;

use App\Entity\DataLocale;
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
        $locale = $entityManager->getRepository(DataLocale::class)->findAll();

        return $this->render('data_locale/index.twig', [
            'locales' => $locale,
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
                    $currentDefaultLocale = $entityManager
                        ->getRepository(DataLocale::class)
                        ->findOneBy([
                            'is_default' => true,
                        ]);
                    if ($currentDefaultLocale !== null) {
                        $currentDefaultLocale->setIsDefault(false);
                    }
                    $locale->setIsDefault(true);
                } else {
                    $locale->setIsDefault(false);
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

        $errors = null;
        if ($request->isMethod('POST')) {
            $locale->setName($request->get('name'));
            $locale->setCode($request->get('code'));
            $locale->setDomain($request->get('domain'));

            $errors = $validation->formatErrors($validator->validate($locale));

            if ($errors === null) {
                $entityManager->persist($locale);

                if ($request->get('default')) {
                    $currentDefaultLocale = $entityManager
                        ->getRepository(DataLocale::class)
                        ->findOneBy([
                            'is_default' => true,
                        ]);
                    if ($currentDefaultLocale !== null) {
                        $currentDefaultLocale->setIsDefault(false);
                    }
                    $locale->setIsDefault(true);
                } else {
                    $locale->setIsDefault(false);
                }

                $entityManager->flush();

                $this->addFlash('success', 'Locale saved');

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
        ]);
    }

    #[
        Route(
            '/__admin/locale/{id}/delete',
            name: 'app_locale_delete',
            requirements: ['id' => '\d+'],
        ),
    ]
    public function delete(
        int $id,
        EntityManagerInterface $entityManager,
    ): Response {
        $locale = $entityManager->getRepository(DataLocale::class)->find($id);
        if ($locale === null) {
            throw new NotFoundHttpException();
        }

        $entityManager->remove($locale);
        $entityManager->flush();

        $this->addFlash('success', 'Locale deleted');

        return $this->redirectToRoute('app_locale');
    }
}
