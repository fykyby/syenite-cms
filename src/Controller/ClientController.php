<?php

namespace App\Controller;

use App\Entity\Page;
use App\Utils\CmsUtils;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class ClientController extends AbstractController
{
    public function index(string $path, EntityManagerInterface $entityManager): Response
    {
        $path = "/{$path}";
        $page = $entityManager->getRepository(Page::class)->findOneBy(['path' => $path]);

        if ($page === null) {
            throw new NotFoundHttpException();
        }

        // TODO: make layout configurable
        $layout = 'base';
        $layoutPath = CmsUtils::getLayoutTemplatePath($layout);

        return $this->render('client/index.twig', [
            'layoutPath' => $layoutPath,
            'page' => $page,
        ]);
    }
}
