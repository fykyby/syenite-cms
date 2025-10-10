<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Page;
use App\Entity\Settings;
use App\Service\Cms;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class ClientController extends AbstractController
{
    public function index(
        string $path,
        EntityManagerInterface $entityManager,
        Cms $cms,
    ): Response {
        $path = "/{$path}";
        $page = $entityManager
            ->getRepository(Page::class)
            ->findOneBy(['path' => $path]);

        if ($page === null) {
            throw new NotFoundHttpException();
        }

        $settings = $entityManager->getRepository(Settings::class)->find(1);
        $layoutData = $settings->getLayoutData();

        $layoutPath = $page->getLayoutName()
            ? $cms->getLayoutTemplatePath($page->getLayoutName())
            : null;

        return $this->render('client/index.twig', [
            'layoutPath' => $layoutPath,
            'layout' => $layoutData,
            'page' => $page,
        ]);
    }
}
