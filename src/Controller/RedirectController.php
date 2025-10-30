<?php

namespace App\Controller;

use App\Entity\DataLocale;
use App\Entity\Redirect;
use App\Service\Validation;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class RedirectController extends AbstractController
{
    #[Route('/__admin/redirects', name: 'app_redirects')]
    public function index(
        EntityManagerInterface $entityManager,
        Request $request,
    ): Response {
        $localeId = $request
            ->getSession()
            ->get(DataLocaleController::SESSION_LOCALE_ID_KEY);
        $redirects = $entityManager
            ->getRepository(Redirect::class)
            ->findBy(['locale' => $localeId]);

        return $this->render('redirect/index.twig', [
            'redirects' => $redirects,
        ]);
    }

    #[Route('/__admin/redirects/new', name: 'app_redirect_new')]
    public function new(
        EntityManagerInterface $entityManager,
        Request $request,
        Validation $validation,
        ValidatorInterface $validator,
    ): Response {
        $errors = null;
        if ($request->isMethod('POST')) {
            $redirect = new Redirect();
            $redirect->setFromPath($request->request->get('fromPath'));
            $redirect->setToPath($request->request->get('toPath'));

            $localeId = $request
                ->getSession()
                ->get(DataLocaleController::SESSION_LOCALE_ID_KEY);
            $redirect->setLocale(
                $entityManager
                    ->getRepository(DataLocale::class)
                    ->find($localeId),
            );

            $errors = $validation->formatErrors(
                $validator->validate($redirect),
            );

            if ($errors === null) {
                $entityManager->persist($redirect);
                $entityManager->flush();

                $this->addFlash('success', 'Redirect created');

                return $this->redirectToRoute('app_redirects');
            } else {
                $this->addFlash('error', 'Validation error(s) occurred');
            }
        }

        return $this->render('redirect/new.twig', [
            'errors' => $errors,
            'values' => $request->request->all(),
        ]);
    }

    #[
        Route(
            '/__admin/redirects/{id}/edit',
            name: 'app_redirect_edit',
            requirements: ['id' => '\d+'],
        ),
    ]
    public function edit(
        int $id,
        EntityManagerInterface $entityManager,
        Request $request,
        Validation $validation,
        ValidatorInterface $validator,
    ): Response {
        $redirect = $entityManager->getRepository(Redirect::class)->find($id);
        if (!$redirect) {
            throw $this->createNotFoundException('Redirect not found');
        }

        $errors = null;
        if ($request->isMethod('POST')) {
            $redirect->setFromPath($request->request->get('fromPath'));
            $redirect->setToPath($request->request->get('toPath'));

            $errors = $validation->formatErrors(
                $validator->validate($redirect),
            );

            if ($errors === null) {
                $entityManager->persist($redirect);
                $entityManager->flush();

                $this->addFlash('success', 'Redirect saved');

                return $this->redirectToRoute('app_redirects');
            } else {
                $this->addFlash('error', 'Validation error(s) occurred');
            }
        }

        return $this->render('redirect/edit.twig', [
            'errors' => $errors,
            'values' => $redirect,
        ]);
    }

    #[
        Route(
            '/redirects/{id}/delete',
            name: 'app_redirect_delete',
            requirements: ['id' => '\d+'],
        ),
    ]
    public function delete(
        int $id,
        EntityManagerInterface $entityManager,
    ): Response {
        $redirect = $entityManager->getRepository(Redirect::class)->find($id);
        if (!$redirect) {
            throw $this->createNotFoundException('Redirect not found');
        }

        $entityManager->remove($redirect);
        $entityManager->flush();

        $this->addFlash('success', 'Redirect deleted');

        return $this->redirectToRoute('app_redirects');
    }
}
