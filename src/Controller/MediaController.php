<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Media;
use App\Service\FileUploader;
use App\Service\ImageUploader;
use App\Service\Validation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class MediaController extends AbstractController
{
    #[Route('/__admin/media', name: 'app_media')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $media = $entityManager->getRepository(Media::class)->findAll();

        return $this->render('media/index.twig', [
            'media' => $media,
        ]);
    }

    #[Route('/__admin/media/new', name: 'app_media_new')]
    public function new(
        Request $request,
        ImageUploader $imageUploader,
        FileUploader $fileUploader,
        EntityManagerInterface $entityManager,
        Validation $validation,
        ValidatorInterface $validator,
    ): Response {
        if (!$request->isMethod('POST')) {
            return $this->render('media/new.twig');
        }

        $files = $request->files->get('media', []);
        if (empty($files)) {
            $this->addFlash('error', 'At least one file is required');

            return $this->render('media/new.twig');
        }

        $error = null;
        foreach ($files as $file) {
            $media = new Media();
            $isImage = str_starts_with($file->getClientMimeType(), 'image/');
            try {
                if ($isImage) {
                    $filename = $imageUploader->upload($file);
                    $media->setName($filename);
                    $media->setType('image');
                    $media->setVariants(
                        $this->generateImageVariants($filename),
                    );
                } else {
                    $filename = $fileUploader->upload($file);
                    $media->setName($filename);
                    $media->setType('file');
                    $media->setVariants($this->generateFileVariants($filename));
                }
            } catch (\Exception $e) {
                $error = 'Error occurred while uploading file';
                break;
            }

            $validationErrors = $validation->formatErrors(
                $validator->validate($media),
            );

            if ($validationErrors) {
                $firstKey = array_key_first($validationErrors);
                $error = $validationErrors[$firstKey];
                break;
            }

            $entityManager->persist($media);
        }

        if ($error === null) {
            $entityManager->flush();
            $this->addFlash('success', 'Media uploaded');

            return $this->redirectToRoute('app_media');
        } else {
            $this->addFlash('error', $error);

            return $this->render('media/new.twig');
        }
    }

    #[
        Route(
            '/__admin/media/{id}/delete',
            name: 'app_media_delete',
            requirements: ['id' => '\d+'],
        ),
    ]
    public function delete(
        int $id,
        EntityManagerInterface $entityManager,
        ImageUploader $imageUploader,
        FileUploader $fileUploader,
    ): Response {
        $media = $entityManager->getRepository(Media::class)->find($id);
        if ($media === null) {
            throw new NotFoundHttpException();
        }

        $entityManager->remove($media);
        $entityManager->flush();

        if ($media->getType() === 'image') {
            $imageUploader->delete($media->getName());
        } else {
            $fileUploader->delete($media->getName());
        }

        $this->addFlash('success', 'Media deleted');

        return $this->redirectToRoute('app_media');
    }

    private function generateFileVariants(string $filename): array
    {
        return [
            'default' => "/media/uploads/{$filename}",
        ];
    }

    private function generateImageVariants(string $filename): array
    {
        return [
            'thumbnail' => "/media/cache/thumbnail/$filename",
            'medium' => "/media/cache/medium/$filename",
            'large' => "/media/cache/large/$filename",
        ];
    }
}
