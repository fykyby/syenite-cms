<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AdminController extends AbstractController
{
    #[Route('/admin', name: 'app_admin')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $userCount = $entityManager->getRepository(User::class)->count();
        if (! $user && $userCount === 0) {
            return $this->render('user/signup.html.twig', []);
        } else if (! $user && $userCount > 0) {
            return $this->render('user/login.html.twig', []);
        }

        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }
}
