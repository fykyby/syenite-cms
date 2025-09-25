<?php

namespace App\Controller;

use App\Service\ImageUploader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class MediaController extends AbstractController
{
    #[Route('/admin/media', name: 'app_media')]
    public function index(): Response
    {
        return $this->render('media/index.twig', []);
    }

    #[Route('/admin/media/new', name: 'app_media_new')]
    public function new(Request $request, ImageUploader $uploader): Response
    {
        $file = $request->files->get('image');

        if (!$file) {
            throw new BadRequestException('No file uploaded');
        }

        $filename = $uploader->upload($file);

        $variants = [
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

        // TODO: create database entry

        return $this->redirectToRoute('app_media');
    }
}
