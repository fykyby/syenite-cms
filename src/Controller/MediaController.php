<?php

namespace App\Controller;

use App\Service\ImageUploader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;

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
        $error = null;
        if ($request->isMethod('POST')) {
            $files = $request->files->get('media');

            if ($files && count($files) > 0) {
                $results = [];

                foreach ($files as $file) {
                    $filename = '';
                    try {
                        $filename = $uploader->upload($file);
                    } catch (\Exception $e) {
                        $error = $e->getMessage();
                        break;
                    }

                    $result = [
                        'name' => $filename,
                        'variants' => [
                            'thumbnail' => $this->generateUrl(
                                'liip_imagine_filter',
                                [
                                    'filter' => 'thumbnail',
                                    'path' => $filename,
                                ],
                            ),
                            'medium' => $this->generateUrl(
                                'liip_imagine_filter',
                                [
                                    'filter' => 'medium',
                                    'path' => $filename,
                                ],
                            ),
                            'large' => $this->generateUrl(
                                'liip_imagine_filter',
                                [
                                    'filter' => 'large',
                                    'path' => $filename,
                                ],
                            ),
                        ],
                    ];

                    $results[] = $result;
                }

                dd($results);
                // TODO: create database entry

                return $this->redirectToRoute('app_media');
            } else {
                $error = 'No file uploaded';
            }
        }

        return $this->render('media/new.twig', [
            'error' => $error,
        ]);
    }
}
