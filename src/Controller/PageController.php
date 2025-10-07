<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Media;
use App\Entity\Page;
use App\Service\Cms;
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
    public function index(EntityManagerInterface $entityManager): Response
    {
        $pages = $entityManager->getRepository(Page::class)->findAll();

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
                $block = $cms->getBlockSchema($data['_name']);
                $pageData[$key]['_path'] = $cms->getBlockTemplatePath(
                    $data['_name'],
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
                $block = $cms->getBlockSchema($data['_name']);
                $block['fields'] = self::attachValuesAndErrors(
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
    ): Response {
        $page = $entityManager->getRepository(Page::class)->find($id);
        if ($page === null) {
            throw new NotFoundHttpException();
        }

        $errors = null;
        if ($request->isMethod('POST')) {
            $page->setPath($request->get('path'));
            $page->setType($request->get('type'));
            $page->setMeta($request->get('meta'));

            $errors = $validation->formatErrors($validator->validate($page));
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
                        $arrayItemErrors = $errors[$key][$index] ?? [];
                        $field['value'][] = [
                            'fields' => self::attachValuesAndErrors(
                                $field['fields'],
                                $item,
                                $arrayItemErrors,
                            ),
                        ];
                    }
                }
            } elseif (
                $field['type'] === 'fieldset' &&
                isset($field['fields'])
            ) {
                $fieldsetData = $data[$key] ?? [];
                $fieldsetErrors = $errors[$key] ?? [];

                $field['fields'] = self::attachValuesAndErrors(
                    $field['fields'],
                    $fieldsetData,
                    $fieldsetErrors,
                );
            } elseif ($field['type'] === 'image') {
                $field['value'] = [
                    'url' => $data[$key]['url'] ?? null,
                    'alt' => $data[$key]['alt'] ?? null,
                    'name' => $data[$key]['name'] ?? null,
                    'variants' => $data[$key]['variants'] ?? null,
                    'type' => $data[$key]['type'] ?? null,
                ];
                $field['error'] = $errors[$key] ?? null;
            } else {
                $field['value'] = $data[$key] ?? null;
                $field['error'] = $errors[$key] ?? null;
            }
        }

        return $fields;
    }

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
                        $nestedResult = self::buildValidationDataAndRules(
                            $field['fields'],
                            $item,
                            $fullKey . '.' . $index,
                        );

                        $validationData[$key][] = $nestedResult['data'];
                        $validationRules = array_merge(
                            $validationRules,
                            $nestedResult['rules'],
                        );
                    }
                }
            } elseif (
                $field['type'] === 'fieldset' &&
                isset($field['fields'])
            ) {
                $validationData[$key] = [];

                $nestedResult = self::buildValidationDataAndRules(
                    $field['fields'],
                    $data[$key] ?? [],
                    $fullKey,
                );

                $validationData[$key] = $nestedResult['data'];
                $validationRules = array_merge(
                    $validationRules,
                    $nestedResult['rules'],
                );
            } elseif ($field['type'] === 'media') {
                $validationData[$key] = [
                    'url' => $data[$key]['url'] ?? null,
                ];

                if (!empty($field['rules'])) {
                    $validationRules[$fullKey . '.url'] = $field['rules'];
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
