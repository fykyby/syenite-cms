<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class MediaController extends AbstractController
{
    #[Route('/admin/media', name: 'app_media')]
    public function index(): Response
    {
        return $this->render('media/index.html.twig', []);
    }
}
