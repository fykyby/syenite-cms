<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Page;
use App\Utils\CmsUtils;
use App\Utils\ValidationUtils;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class PageController extends AbstractController
{
    #[Route('/admin/pages', name: 'app_pages')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $pages = $entityManager->getRepository(Page::class)->findAll();

        return $this->render('page/index.twig', [
            'pages' => $pages,
        ]);
    }

    #[Route('/admin/pages/new', name: 'app_pages_new')]
    public function new(Request $request, ValidatorInterface $validator, EntityManagerInterface $entityManager): Response
    {
        $errors = null;
        if ($request->isMethod('POST')) {
            $page = new Page();
            $page->setPath($request->get('path'));
            $page->setType($request->get('type'));
            $page->setData([]);

            $errors = ValidationUtils::formatErrors($validator->validate($page));

            if ($errors === null) {
                $entityManager->persist($page);
                $entityManager->flush();

                return $this->redirectToRoute('app_page', ['id' => $page->getId()]);
            }
        }

        return $this->render('page/new.twig', [
            'errors' => $errors,
            'values' => $request->request->all(),
        ]);
    }

    #[Route('/admin/pages/{id}', name: 'app_page', requirements: ['id' => '\d+'])]
    public function edit(int $id, EntityManagerInterface $entityManager): Response
    {
        $page = $entityManager->getRepository(Page::class)->find($id);
        if ($page === null) {
            throw new NotFoundHttpException();
        }

        $blocks = CmsUtils::listBlocks();

        return $this->render('page/edit.twig', [
            'page' => $page,
            'blocks' => $blocks,
        ]);
    }

    #[Route('/admin/pages/blocks/{type}', name: 'app_page_block')]
    public function block(string $type, Request $request): Response
    {
        $block = CmsUtils::getBlockData($type);
        $index = $request->query->get('index');
        if ($block === null || $index === null) {
            throw new NotFoundHttpException();
        }

        return $this->render('page/_block_fieldset.twig', [
            'block' => $block,
            'index' => $index,
        ]);
    }

    #[Route('/admin/pages/{id}/delete', name: 'app_page_delete', requirements: ['id' => '\d+'])]
    public function delete(int $id, EntityManagerInterface $entityManager): Response
    {
        $page = $entityManager->getRepository(Page::class)->find($id);
        if ($page === null) {
            throw new NotFoundHttpException();
        }

        $entityManager->remove($page);
        $entityManager->flush();

        return $this->redirectToRoute('app_pages');
    }
}
