<?php

namespace App\Controller;

use App\Entity\Media;
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
    #[Route('/admin/media', name: 'app_media')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $media = $entityManager->getRepository(Media::class)->findAll();

        return $this->render('media/index.twig', [
            'media' => $media,
        ]);
    }

    #[Route('/admin/media/new', name: 'app_media_new')]
    public function new(
        Request $request,
        ImageUploader $uploader,
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
            return $this->redirectToRoute('app_media_new');
        }

        $error = null;

        foreach ($files as $file) {
            try {
                $filename = $uploader->upload($file);
            } catch (\Exception $e) {
                $this->addFlash('error', 'Error occurred while uploading file');
                break;
            }

            $variants = $this->generateVariants($filename);

            $image = new Media();
            $image->setType('image');
            $image->setName($filename);
            $image->setVariants($variants);

            $validationErrors = $validation->formatErrors(
                $validator->validate($image),
            );

            if ($validationErrors) {
                $firstKey = array_key_first($validationErrors);
                $error = $validationErrors[$firstKey];
                break;
            }

            $entityManager->persist($image);
        }

        if ($error === null) {
            $entityManager->flush();
            $this->addFlash('success', 'Media uploaded');
            return $this->redirectToRoute('app_media');
        } else {
            $this->addFlash('error', $error);
            return $this->redirectToRoute('app_media_new');
        }
    }

    #[
        Route(
            '/admin/media/{id}/delete',
            name: 'app_media_delete',
            requirements: ['id' => '\d+'],
        ),
    ]
    public function delete(
        int $id,
        EntityManagerInterface $entityManager,
        ImageUploader $imageUploader,
    ): Response {
        $media = $entityManager->getRepository(Media::class)->find($id);
        if ($media === null) {
            throw new NotFoundHttpException();
        }

        $entityManager->remove($media);
        $entityManager->flush();

        $imageUploader->delete($media->getName());

        $this->addFlash('success', 'Media deleted');

        return $this->redirectToRoute('app_media');
    }

    private function generateVariants(string $filename): array
    {
        return [
            'thumbnail' => $this->generateUrl('liip_imagine_filter', [
                'filter' => 'thumbnail',
                'path' => $filename,
            ]),
            'medium' => $this->generateUrl('liip_imagine_filter', [
                'filter' => 'medium',
                'path' => $filename,
            ]),
            'large' => $this->generateUrl('liip_imagine_filter', [
                'filter' => 'large',
                'path' => $filename,
            ]),
        ];
    }
}
