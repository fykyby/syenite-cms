<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\DataLocale;
use App\Entity\LayoutData;
use App\Entity\Page;
use App\Service\Cms;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class ClientController extends AbstractController
{
    public function index(
        string $path,
        EntityManagerInterface $entityManager,
        Cms $cms,
        Request $request,
    ): Response {
        $requestDomain = $request->getHost();
        $locale = $entityManager->getRepository(DataLocale::class)->findOneBy([
            'domain' => $requestDomain,
        ]);
        if ($locale === null) {
            $locale = $entityManager
                ->getRepository(DataLocale::class)
                ->findOneBy([
                    'isDefault' => true,
                ]);
        }

        $path = "/{$path}";
        $page = $entityManager->getRepository(Page::class)->findOneBy([
            'path' => $path,
            'locale' => $locale,
        ]);
        if ($page === null) {
            throw new NotFoundHttpException();
        }

        $layoutData = $entityManager
            ->getRepository(LayoutData::class)
            ->findOneBy([
                'name' => $page->getLayoutName(),
                'theme' => $cms->getThemeName(),
                'locale' => $locale,
            ]);

        $layoutPath = $page->getLayoutName()
            ? $cms->getLayoutTemplatePath($page->getLayoutName())
            : null;

        return $this->render('client/index.twig', [
            'layoutPath' => $layoutPath,
            'layout' => $layoutData?->getData(),
            'page' => $page,
        ]);
    }
}
