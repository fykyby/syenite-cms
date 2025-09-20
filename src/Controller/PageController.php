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
    public function edit(int $id, EntityManagerInterface $entityManager, Request $request): Response
    {
        $page = $entityManager->getRepository(Page::class)->find($id);
        if ($page === null) {
            throw new NotFoundHttpException();
        }

        $blocks = [];
        $hasErrors = false;
        if ($request->isMethod('POST')) {
            $pageData = $request->request->all()['blocks'] ?? [];


            foreach ($pageData as $data) {
                $block = CmsUtils::getBlockData($data['_type']);

                $built = self::buildValidationDataAndRules($block['fields'], $data);
                $validationData = $built['data'];
                $validationRules = $built['rules'];

                $blockErrors = ValidationUtils::validate($validationData, $validationRules) ?? [];
                $hasErrors = array_filter($blockErrors);
                $block['fields'] = self::attachValuesAndErrors($block['fields'], $data, $blockErrors);
                $blocks[] = $block;
            }

            $page->setData($pageData);

            if (! $hasErrors) {
                $entityManager->flush();
                return $this->redirectToRoute('app_page', ['id' => $id]);
            }
        } else {
            foreach ($page->getData() as $data) {
                $block = CmsUtils::getBlockData($data['_type']);
                $block['fields'] = self::attachValuesAndErrors($block['fields'], $data, []);
                $blocks[] = $block;
            }
        }

        $blockList = CmsUtils::listBlocks();
        return $this->render('page/edit.twig', [
            "path" => $page->getPath(),
            'blocks' => $blocks,
            'blockList' => $blockList,
        ]);
    }

    #[Route('/admin/pages/blocks/{type}', name: 'app_pages_block')]
    public function block(string $type): Response
    {
        $block = CmsUtils::getBlockData($type);
        if ($block === null) {
            throw new NotFoundHttpException();
        }

        return $this->json($block);
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

    private static function attachValuesAndErrors(array $fields, array $data, array $errors, string $prefix = ''): array
    {
        foreach ($fields as &$field) {
            $key = $field['key'];
            $fullKey = $prefix === '' ? $key : $prefix.'.'.$key;

            if ($field['type'] === 'array' && isset($field['fields'])) {
                // Array field: each item becomes a nested 'fields' group
                $field['value'] = [];

                if (isset($data[$key]) && is_array($data[$key])) {
                    foreach ($data[$key] as $index => $item) {
                        $field['value'][] = [
                            'fields' => self::attachValuesAndErrors(
                                $field['fields'],
                                $item,
                                $errors,
                                $fullKey.'.'.$index
                            )
                        ];
                    }
                }

                // Remove the base 'fields' template since it's now expanded in value
                unset($field['fields']);
            } else {
                // Leaf field: attach value
                $field['value'] = $data[$key] ?? null;

                // Attach error if present
                if (isset($errors[$fullKey])) {
                    $field['error'] = $errors[$fullKey];
                }
            }
        }

        return $fields;
    }

    private static function buildValidationDataAndRules(array $fields, array $data, string $prefix = ''): array
    {
        $validationData = [];
        $validationRules = [];

        foreach ($fields as $field) {
            $key = $field['key'];
            $fullKey = $prefix === '' ? $key : $prefix.'.'.$key;

            if ($field['type'] === 'array' && isset($field['fields'])) {
                // Add wildcard rules for nested fields
                foreach ($field['fields'] as $subField) {
                    $validationRules[$fullKey.'.*.'.$subField['key']] = $subField['rules'] ?? '';
                }

                // Collect actual values for validation
                if (isset($data[$key]) && is_array($data[$key])) {
                    foreach ($data[$key] as $index => $item) {
                        $child = self::buildValidationDataAndRules($field['fields'], $item, $fullKey.'.'.$index);
                        $validationData = array_merge($validationData, $child['data']);
                    }
                }
            } else {
                // Leaf field: add value and rule
                $validationData[$fullKey] = $data[$key] ?? null;
                if (! empty($field['rules'])) {
                    $validationRules[$fullKey] = $field['rules'];
                }
            }
        }

        return ['data' => $validationData, 'rules' => $validationRules];
    }

    // private static function flattenArray(array $array, string $prefix = ''): array
    // {
    //     $result = [];

    //     foreach ($array as $key => $value) {
    //         $key = (string) $key;
    //         $newKey = $prefix === '' ? $key : $prefix.'['.$key.']';

    //         if (is_array($value)) {
    //             $result = array_merge($result, self::flattenArray($value, $newKey));
    //         } else {
    //             $result[$newKey] = $value;
    //         }
    //     }

    //     return $result;
    // }
}
