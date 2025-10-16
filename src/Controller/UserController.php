<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\DataLocale;
use App\Entity\LayoutData;
use App\Entity\Settings;
use App\Entity\User;
use App\Service\Cms;
use App\Service\Validation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class UserController extends AbstractController
{
    #[Route('/__admin/auth/signup', name: 'app_auth_signup')]
    public function signup(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        Validation $validation,
        Cms $cms,
    ): Response {
        $userCount = $entityManager->getRepository(User::class)->count();
        if ($userCount > 0) {
            return $this->redirectToRoute('app_auth_login');
        }

        $errors = null;
        if ($request->isMethod('POST')) {
            $user = new User();
            $user->setEmail($request->get('email'));
            $user->setPassword($request->get('password'));
            $user->setRoles(['ROLE_SUPER_ADMIN']);
            $entityManager->persist($user);

            $errors = $validation->formatErrors($validator->validate($user));
            if (
                $request->get('password') !== $request->get('passwordconfirm')
            ) {
                $errors['passwordconfirm'] = 'Passwords do not match';
            }

            if ($errors === null) {
                $user->setPassword(
                    $passwordHasher->hashPassword(
                        $user,
                        $request->get('password'),
                    ),
                );

                $settings = new Settings();
                $entityManager->persist($settings);

                $themes = $cms->listThemes();
                if (count($themes) === 0) {
                    throw new \Exception('No themes found');
                }
                $settings->setCurrentTheme($themes[0]);

                $defaultLocale = new DataLocale();
                $defaultLocale->setName('Default');
                $defaultLocale->setIsDefault(true);
                $entityManager->persist($defaultLocale);

                foreach ($cms->listLayouts() as $layoutName) {
                    $layoutData = new LayoutData();
                    $layoutData->setName($layoutName);
                    $layoutData->setTheme($cms->getThemeName());
                    $layoutData->setLocale($defaultLocale);
                    $layoutData->setData([]);
                    $entityManager->persist($layoutData);
                }

                $entityManager->flush();

                return $this->redirectToRoute('app_auth_login');
            }
        }

        return $this->render('user/signup.twig', [
            'errors' => $errors,
            'values' => $request->request->all(),
        ]);
    }

    #[Route('/__admin/auth/login', name: 'app_auth_login')]
    public function login(
        EntityManagerInterface $entityManager,
        AuthenticationUtils $authUtils,
    ): Response {
        $userCount = $entityManager->getRepository(User::class)->count();
        if ($userCount === 0) {
            return $this->redirectToRoute('app_auth_signup');
        }

        $error = $authUtils->getLastAuthenticationError();
        $lastEmail = $authUtils->getLastUsername();

        return $this->render('user/login.twig', [
            'last_email' => $lastEmail,
            'error' => $error,
        ]);
    }

    #[Route('/__admin/auth/logout', name: 'app_auth_logout')]
    public function logout(): Response
    {
        return $this->render('user/login.twig', []);
    }
}
