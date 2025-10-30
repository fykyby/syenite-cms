<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Page;
use App\Entity\DataLocale;
use App\Entity\LayoutData;
use App\Service\Cms;
use App\Service\SitemapManager;
use App\Service\Validation;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class DataLocaleController extends AbstractController
{
    public const string SESSION_LOCALE_ID_KEY = '__localeId';

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
        Cms $cms,
    ): Response {
        $errors = null;
        if ($request->isMethod('POST')) {
            $locale = new DataLocale();
            $locale->setName($request->get('name'));
            $locale->setDomain($request->get('domain'));

            $errors = $validation->formatErrors($validator->validate($locale));

            if ($errors === null) {
                $entityManager->persist($locale);

                if ($request->get('default')) {
                    $oldDefaultLocale = $entityManager
                        ->getRepository(DataLocale::class)
                        ->findOneBy([
                            'isDefault' => true,
                        ]);

                    if ($oldDefaultLocale !== null) {
                        $oldDefaultLocale->setIsDefault(false);
                    }

                    $locale->setIsDefault(true);
                } else {
                    $locale->setIsDefault(false);
                }

                foreach ($cms->listLayouts() as $layoutName) {
                    $layoutData = new LayoutData();
                    $layoutData->setName($layoutName);
                    $layoutData->setTheme($cms->getThemeName());
                    $layoutData->setLocale($locale);
                    $layoutData->setData([]);
                    $entityManager->persist($layoutData);
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
        $localeReposiotory = $entityManager->getRepository(DataLocale::class);
        $locale = $localeReposiotory->find($id);
        if ($locale === null) {
            throw $this->createNotFoundException();
        }

        $errors = null;
        if ($request->isMethod('POST')) {
            $locale->setName($request->get('name'));
            $locale->setDomain($request->get('domain'));

            $errors = $validation->formatErrors($validator->validate($locale));

            if ($errors === null) {
                $entityManager->persist($locale);

                if ($request->get('default')) {
                    $oldDefaultLocale = $localeReposiotory->findOneBy([
                        'isDefault' => true,
                    ]);

                    if ($oldDefaultLocale !== null) {
                        $oldDefaultLocale->setIsDefault(false);
                    }

                    $locale->setIsDefault(true);
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
            'values' => $locale,
        ]);
    }

    #[Route('/__admin/locale/{id}/select', name: 'app_locale_change')]
    public function change(
        int $id,
        Request $request,
        EntityManagerInterface $entityManager,
        RouterInterface $router,
    ): Response {
        $locale = $entityManager->getRepository(DataLocale::class)->find($id);
        if ($locale === null) {
            throw $this->createNotFoundException();
        }

        $request->getSession()->set(self::SESSION_LOCALE_ID_KEY, $id);

        $referer = $request->headers->get('referer');
        if (!$referer) {
            return $this->redirectToRoute('app_dashboard');
        }

        $path = parse_url($referer, PHP_URL_PATH);
        $parameters = $router->match($path);

        if (
            $parameters['_route'] === 'app_page' ||
            $parameters['_route'] === 'app_page_edit'
        ) {
            $previousPage = $entityManager
                ->getRepository(Page::class)
                ->find($parameters['id']);
            if (!$previousPage) {
                return $this->redirectToRoute('app_pages');
            }

            $targetPage = $entityManager
                ->getRepository(Page::class)
                ->findOneBy([
                    'locale' => $locale,
                    'path' => $previousPage->getPath(),
                ]);
            if (!$targetPage) {
                return $this->redirectToRoute('app_pages');
            }

            return $this->redirectToRoute($parameters['_route'], [
                'id' => $targetPage->getId(),
            ]);
        }

        return $this->redirect($referer);
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
        Request $request,
        SitemapManager $sitemapManager,
    ): Response {
        $localeRepository = $entityManager->getRepository(DataLocale::class);
        $locale = $localeRepository->find($id);
        if ($locale === null) {
            throw $this->createNotFoundException();
        }

        $count = $localeRepository->count();
        if ($locale->isDefault() || $count === 1) {
            throw new BadRequestHttpException();
        }

        $entityManager->remove($locale);
        $sitemapManager->delete($locale->getId());

        $session = $request->getSession();
        if (
            intval($session->get(self::SESSION_LOCALE_ID_KEY)) ===
            $locale->getId()
        ) {
            $defaultLocale = $localeRepository->findOneBy([
                'isDefault' => true,
            ]);
            $session->set(self::SESSION_LOCALE_ID_KEY, $defaultLocale->getId());
        }

        $entityManager->flush();

        $this->addFlash('success', 'Locale deleted');

        return $this->redirectToRoute('app_locale');
    }
}
