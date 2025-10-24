<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\DataLocale;
use App\Entity\LayoutData;
use App\Entity\User;
use App\Service\Cms;
use App\Service\PasswordResetService;
use App\Service\Validation;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class UserController extends AbstractController
{
    public function __construct(
        private CacheItemPoolInterface $localeCachePool,
    ) {}

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

                $defaultLocale = new DataLocale();
                $defaultLocale->setName('English');
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
                $this->localeCachePool->clear();

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

    #[Route('/__admin/auth/password-reset', name: 'app_auth_password_reset')]
    public function passwordReset(
        Request $request,
        Validation $validation,
        EntityManagerInterface $entityManager,
        PasswordResetService $passwordResetService,
    ): Response {
        $userCount = $entityManager->getRepository(User::class)->count();
        if ($userCount === 0) {
            return $this->redirectToRoute('app_auth_signup');
        }

        $values = $request->request->all();
        $errors = [];
        if ($request->isMethod('POST')) {
            $email = $request->request->get('email');
            $errors = $validation->validate(
                ['email' => $email],
                ['email' => 'required|email'],
            );

            $user = $entityManager->getRepository(User::class)->findOneBy([
                'email' => $email,
            ]);

            if ($user === null) {
                $errors['email'] = 'User not found';
            }

            if (empty($errors)) {
                $passwordResetService->sendResetEmail($user);
                return $this->render('user/password_reset_success.twig', []);
            }
        } elseif ($this->getUser()) {
            $values['email'] = $this->getUser()->getUserIdentifier();
        }

        return $this->render('user/password_reset.twig', [
            'errors' => $errors,
            'values' => $values,
        ]);
    }

    #[
        Route(
            '/__admin/auth/password-reset/{signature}',
            name: 'app_auth_password_reset_target',
        ),
    ]
    public function passwordResetTarget(
        string $signature,
        EntityManagerInterface $entityManager,
        Validation $validation,
        Request $request,
        PasswordResetService $passwordResetService,
        UserPasswordHasherInterface $passwordHasher,
    ): Response {
        $userCount = $entityManager->getRepository(User::class)->count();
        if ($userCount === 0) {
            return $this->redirectToRoute('app_auth_signup');
        }

        $userId = $request->query->get('userId');
        $expires = $request->query->get('expires');
        $expectedSignature = $passwordResetService->getSignature(
            $userId,
            $expires,
        );

        if (!hash_equals($expectedSignature, $signature)) {
            throw $this->createAccessDeniedException();
        }

        if ($expires < time()) {
            throw $this->createAccessDeniedException();
        }

        $errors = null;
        if ($request->isMethod('POST')) {
            $errors = $validation->validate(
                ['password' => $request->get('password')],
                ['password' => 'required|min:6|max:128'],
            );

            if (
                $request->get('password') !== $request->get('passwordconfirm')
            ) {
                $errors['passwordconfirm'] = 'Passwords do not match';
            }

            $user = $entityManager->getRepository(User::class)->find($userId);
            if ($user === null) {
                throw $this->createNotFoundException();
            }

            if ($errors === null) {
                $user->setPassword(
                    $passwordHasher->hashPassword(
                        $user,
                        $request->get('password'),
                    ),
                );

                $entityManager->flush();

                return $this->redirectToRoute('app_auth_login');
            }
        }

        return $this->render('user/password_reset_target.twig', [
            'errors' => $errors,
            'values' => $request->request->all(),
        ]);
    }
}
