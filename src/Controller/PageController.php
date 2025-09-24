<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Page;
use App\Service\Cms;
use App\Service\Validation;
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
    public function new(
        Request $request,
        ValidatorInterface $validator,
        EntityManagerInterface $entityManager,
        Validation $validation,
    ): Response {
        $errors = null;
        if ($request->isMethod('POST')) {
            $page = new Page();
            $page->setPath($request->get('path'));
            $page->setType($request->get('type'));
            $page->setData([]);
            $page->setMeta([]);

            $errors = $validation->formatErrors($validator->validate($page));
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
        ]);
    }

    #[
        Route(
            '/admin/pages/{id}',
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
    ): Response {
        $page = $entityManager->getRepository(Page::class)->find($id);
        if ($page === null) {
            throw new NotFoundHttpException();
        }

        $blocks = [];
        $hasErrors = false;
        if ($request->isMethod('POST')) {
            $pageData = $request->request->all()['blocks'] ?? [];

            foreach ($pageData as $key => $data) {
                $block = $cms->getBlockData($data['_type']);
                $pageData[$key]['_path'] = $cms->getBlockTemplatePath(
                    $data['_type'],
                );

                $built = self::buildValidationDataAndRules(
                    $block['fields'],
                    $data,
                );
                $validationData = $built['data'];
                $validationRules = $built['rules'];

                $blockErrors =
                    $validation->validate($validationData, $validationRules) ??
                    [];
                $hasErrors = $hasErrors || !empty($blockErrors);

                $block['fields'] = self::attachValuesAndErrors(
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
                $block = $cms->getBlockData($data['_type']);
                $block['fields'] = self::attachValuesAndErrors(
                    $block['fields'],
                    $data,
                    [],
                );
                $blocks[] = $block;
            }
        }

        $blockList = $cms->listBlocks();
        return $this->render('page/edit.twig', [
            'blockList' => $blockList,
            'page' => $page,
            'blocks' => $blocks,
        ]);
    }

    #[
        Route(
            '/admin/pages/{id}/delete',
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

    #[Route('/admin/pages/blocks/{type}', name: 'app_page_block')]
    public function block(string $type, Cms $cms): Response
    {
        $block = $cms->getBlockData($type);
        if ($block === null) {
            throw new NotFoundHttpException();
        }

        return $this->json($block);
    }

    #[
        Route(
            '/admin/pages/{id}/meta',
            name: 'app_page_meta',
            requirements: ['id' => '\d+'],
        ),
    ]
    public function meta(
        int $id,
        EntityManagerInterface $entityManager,
        Request $request,
    ): Response {
        $page = $entityManager->getRepository(Page::class)->find($id);
        if ($page === null) {
            throw new NotFoundHttpException();
        }

        $data = $request->request->all();
        $page->setMeta($data);
        $entityManager->flush();

        $this->addFlash('success', 'Meta saved');

        return $this->json($data);
    }

    /**
     * @param array<int,mixed> $fields
     * @param array<int,mixed> $data
     * @param array<int,mixed> $errors
     * @return array<int,mixed>
     */
    private static function attachValuesAndErrors(
        array $fields,
        array $data,
        array $errors,
    ): array {
        foreach ($fields as &$field) {
            $key = $field['key'];

            if ($field['type'] === 'array' && isset($field['fields'])) {
                $field['value'] = [];

                if (!empty($data[$key]) && is_array($data[$key])) {
                    foreach ($data[$key] as $index => $item) {
                        // Handle nested array errors
                        $arrayItemErrors = [];

                        // Look for errors at this level (items.0, items.1, etc.)
                        if (isset($errors[$key][$index])) {
                            $arrayItemErrors = $errors[$key][$index];
                        }

                        $field['value'][] = [
                            'fields' => self::attachValuesAndErrors(
                                $field['fields'],
                                $item,
                                $arrayItemErrors,
                            ),
                        ];
                    }
                }
            } else {
                $field['value'] = $data[$key] ?? null;
                $field['error'] = $errors[$key] ?? null;
            }
        }

        return $fields;
    }

    /**
     * @param array<int,mixed> $fields
     * @param array<int,mixed> $data
     * @return array<string,mixed>
     */
    private static function buildValidationDataAndRules(
        array $fields,
        array $data,
        string $prefix = '',
    ): array {
        $validationData = [];
        $validationRules = [];

        foreach ($fields as $field) {
            $key = $field['key'];
            $fullKey = $prefix === '' ? $key : $prefix . '.' . $key;

            if ($field['type'] === 'array' && isset($field['fields'])) {
                $validationData[$key] = [];

                if (!empty($data[$key]) && is_array($data[$key])) {
                    foreach ($data[$key] as $index => $item) {
                        // Recursively build validation for nested arrays
                        $nestedResult = self::buildValidationDataAndRules(
                            $field['fields'],
                            $item,
                            $fullKey . '.' . $index,
                        );

                        // Merge the nested validation data
                        $validationData[$key][] = $nestedResult['data'];

                        // Merge the nested validation rules
                        $validationRules = array_merge(
                            $validationRules,
                            $nestedResult['rules'],
                        );
                    }
                }
            } else {
                $validationData[$key] = $data[$key] ?? null;
                if (!empty($field['rules'])) {
                    $validationRules[$fullKey] = $field['rules'];
                }
            }
        }

        return ['data' => $validationData, 'rules' => $validationRules];
    }
}
