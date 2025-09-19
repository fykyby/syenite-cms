<?php

namespace App\Controller;

use App\Entity\Page;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

final class ClientController extends AbstractController
{
    public function index(string $path, EntityManagerInterface $entityManager): Response
    {
        $path = "/{$path}";

        $page = $entityManager->getRepository(Page::class)->findOneBy(['path' => $path]);
        dd($page);
    }
}
