<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\DataLocale;
use App\Entity\Media;
use App\Entity\Page;
use App\Service\Cms;
use App\Service\DataLocaleService;
use App\Service\DataTransformer;
use App\Service\Validation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class PageController extends AbstractController
{
    #[Route('/__admin/pages', name: 'app_pages')]
    public function index(
        EntityManagerInterface $entityManager,
        Request $request,
    ): Response {
        $locale = $request->getSession()->get('__locale');
        $pages = $entityManager->getRepository(Page::class)->findBy([
            'locale' => $locale,
        ]);

        return $this->render('page/index.twig', [
            'pages' => $pages,
        ]);
    }

    #[Route('/__admin/pages/new', name: 'app_pages_new')]
    public function new(
        Request $request,
        ValidatorInterface $validator,
        EntityManagerInterface $entityManager,
        Validation $validation,
        Cms $cms,
    ): Response {
        $locale = $request->getSession()->get('__locale');
        $layouts = $cms->listLayouts();

        $errors = null;
        if ($request->isMethod('POST')) {
            $page = new Page();
            $page->setPath($request->get('path'));
            $page->setType($request->get('type'));
            $page->setMeta($request->get('meta'));
            $page->setLocale(
                $entityManager->getRepository(DataLocale::class)->find($locale),
            );
            $page->setData([]);

            $errors = $validation->formatErrors($validator->validate($page));

            $newLayoutName = $request->get('layout');
            if ($newLayoutName === '') {
                $page->setLayoutName(null);
            } elseif (in_array($newLayoutName, $layouts)) {
                $page->setLayoutName($newLayoutName);
            } else {
                $errors['layout'] = 'Invalid layout';
            }

            if ($errors === null) {
                $entityManager->persist($page);
                $entityManager->flush();

                $this->addFlash('success', 'Page created');

                return $this->redirectToRoute('app_page', [
                    'id' => $page->getId(),
                ]);
            } else {
                $this->addFlash('error', 'Validation error(s) occurred');
            }
        }

        return $this->render('page/new.twig', [
            'errors' => $errors,
            'values' => $request->request->all(),
            'layouts' => $layouts,
        ]);
    }

    #[
        Route(
            '/__admin/pages/{id}',
            name: 'app_page',
            requirements: ['id' => '\d+'],
        ),
    ]
    public function edit(
        int $id,
        EntityManagerInterface $entityManager,
        Request $request,
        Cms $cms,
        Validation $validation,
        SerializerInterface $serializer,
        DataTransformer $dataTransformer,
    ): Response {
        $locale = $request->getSession()->get('__locale');
        $page = $entityManager->getRepository(Page::class)->findOneBy([
            'id' => $id,
            'locale' => $locale,
        ]);
        if ($page === null) {
            return $this->redirectToRoute('app_pages');
        }

        $blocks = [];
        $hasErrors = false;
        if ($request->isMethod('POST')) {
            $pageData = $request->request->all()['blocks'] ?? [];

            foreach ($pageData as $key => $data) {
                $block = $cms->getBlockSchema($data['_name']);

                $pageData[$key]['_path'] = $cms->getBlockTemplatePath(
                    $data['_name'],
                );

                $built = $dataTransformer->buildValidationDataAndRules(
                    $block['fields'],
                    $data,
                );

                $validationData = $built['data'];
                $validationRules = $built['rules'];

                $blockErrors =
                    $validation->validate($validationData, $validationRules) ??
                    [];
                $hasErrors = $hasErrors || !empty($blockErrors);

                $block['fields'] = $dataTransformer->attachValuesAndErrors(
                    $block['fields'],
                    $data,
                    $blockErrors,
                );
                $blocks[] = $block;
            }

            $page->setData($pageData);
            if (!$hasErrors) {
                $entityManager->flush();

                $this->addFlash('success', 'Page saved');

                return $this->redirectToRoute('app_page', ['id' => $id]);
            } else {
                $this->addFlash('error', 'Validation error(s) occurred');
            }
        } else {
            foreach ($page->getData() as $data) {
                $block = $cms->getBlockSchema($data['_name']);
                if ($block === null) {
                    continue;
                }

                $block['fields'] = $dataTransformer->attachValuesAndErrors(
                    $block['fields'],
                    $data,
                    [],
                );
                $blocks[] = $block;
            }
        }

        $media = $entityManager->getRepository(Media::class)->findAll();
        $mediaJson = $serializer->serialize($media, 'json');

        $blockList = $cms->listBlocks();
        return $this->render('page/edit.twig', [
            'blockList' => $blockList,
            'page' => $page,
            'blocks' => $blocks,
            'media' => $mediaJson,
        ]);
    }

    #[
        Route(
            '/__admin/pages/{id}/edit',
            name: 'app_page_edit',
            requirements: ['id' => '\d+'],
        ),
    ]
    public function editDetails(
        int $id,
        EntityManagerInterface $entityManager,
        Request $request,
        Validation $validation,
        ValidatorInterface $validator,
        Cms $cms,
    ): Response {
        $page = $entityManager->getRepository(Page::class)->find($id);
        if ($page === null) {
            throw new NotFoundHttpException();
        }

        $layouts = $cms->listLayouts();
        $errors = null;
        if ($request->isMethod('POST')) {
            $page->setPath($request->get('path'));
            $page->setType($request->get('type'));
            $page->setMeta($request->get('meta'));

            $errors = $validation->formatErrors($validator->validate($page));

            $newLayoutName = $request->get('layout');
            if ($newLayoutName === '') {
                $page->setLayoutName(null);
            } elseif (in_array($newLayoutName, $layouts)) {
                $page->setLayoutName($newLayoutName);
            } else {
                $errors['layout'] = 'Invalid layout';
            }

            if ($errors === null) {
                $entityManager->flush();

                $this->addFlash('success', 'Page saved');

                return $this->redirectToRoute('app_page_edit', [
                    'id' => $page->getId(),
                ]);
            } else {
                $this->addFlash('error', 'Validation error(s) occurred');
            }
        }

        return $this->render('page/edit_details.twig', [
            'page' => $page,
            'layouts' => $layouts,
            'errors' => $errors,
        ]);
    }

    #[
        Route(
            '/__admin/pages/{id}/delete',
            name: 'app_page_delete',
            requirements: ['id' => '\d+'],
        ),
    ]
    public function delete(
        int $id,
        EntityManagerInterface $entityManager,
    ): Response {
        $page = $entityManager->getRepository(Page::class)->find($id);
        if ($page === null) {
            throw new NotFoundHttpException();
        }

        $entityManager->remove($page);
        $entityManager->flush();

        $this->addFlash('success', 'Page deleted');

        return $this->redirectToRoute('app_pages');
    }

    #[Route('/__admin/pages/blocks/{name}', name: 'app_page_block')]
    public function block(string $name, Cms $cms): Response
    {
        $block = $cms->getBlockSchema($name);
        if ($block === null) {
            throw new NotFoundHttpException();
        }

        return $this->json($block);
    }
}
