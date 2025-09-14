<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

final class UserController extends AbstractController
{
    #[Route('/admin/auth/signup', name: 'app_auth_signup', methods: ['POST'])]
    public function signup(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager): Response
    {
        $userCount = $entityManager->getRepository(User::class)->count();
        if ($userCount > 0) {
            return $this->render('user/login.html.twig', []);
        }

        $user = new User();

        $password = '';
        $hashedPassword = $passwordHasher->hashPassword($user, $password);
        $user->setPassword($hashedPassword);

        return $this->render('user/signup.html.twig', []);
    }

    #[Route('/admin/auth/login', name: 'app_auth_login', methods: ['POST'])]
    public function login(Security $security): Response
    {
        $user = new User();

        $security->login($user);

        return $this->render('user/login.html.twig', []);
    }


    #[Route('/admin/auth/logout', name: 'app_auth_logout', methods: ['POST'])]
    public function logout(Security $security): Response
    {
        $response = $security->logout();

        return $this->render('user/login.html.twig', []);
    }
}
