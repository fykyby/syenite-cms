<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SettingsController extends AbstractController
{
    #[Route('/__admin/settings', name: 'app_settings')]
    public function index(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            dd($request->request->all());
        }
        return $this->render('settings/index.twig', []);
    }
}
