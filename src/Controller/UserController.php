<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\LogInType;
use App\Form\SignUpType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

final class UserController extends AbstractController
{
    #[Route('/admin/auth/signup', name: 'app_auth_signup')]
    public function signup(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager): Response
    {
        $userCount = $entityManager->getRepository(User::class)->count();
        if ($userCount > 0) {
            return $this->redirectToRoute('app_auth_login');
        }

        $user = new User();
        $form = $this->createForm(SignUpType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $hashedPassword = $passwordHasher->hashPassword($user, $form->get('password')->getData());
            $user->setPassword($hashedPassword);
            $user->setRoles(['ROLE_SUPER_ADMIN']);
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_auth_login');
        }


        return $this->render('user/signup.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/admin/auth/login', name: 'app_auth_login')]
    public function login(Security $security, EntityManagerInterface $entityManager, Request $request, AuthenticationUtils $authenticationUtils): Response
    {
        $userCount = $entityManager->getRepository(User::class)->count();
        if ($userCount === 0) {
            return $this->redirectToRoute('app_auth_signup');
        }

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        // $user = new User();
        // $form->handleRequest($request);
        // if ($form->isSubmitted() && $form->isValid()) {
        //     dd($user);
        //     $security->login($user, 'form_login');
        // }

        return $this->render('user/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }


    #[Route('/admin/auth/logout', name: 'app_auth_logout')]
    public function logout(Security $security): Response
    {
        $response = $security->logout();

        return $this->render('user/login.html.twig', []);
    }
}
