<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\DataLocale;
use App\Entity\LayoutData;
use App\Entity\Page;
use App\Service\Cms;
use App\Service\Validation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpKernel\Controller\ArgumentResolverInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Routing\Attribute\Route;

final class ClientController extends AbstractController
{
    public function __construct(
        private ContainerInterface $fullContainer,
        private ArgumentResolverInterface $argumentResolver,
    ) {}

    public function index(
        string $path,
        EntityManagerInterface $entityManager,
        Cms $cms,
        Request $request,
    ): Response {
        $requestDomain = $request->getHost();
        $locale = $entityManager->getRepository(DataLocale::class)->findOneBy([
            'domain' => $requestDomain,
        ]);
        if ($locale === null) {
            $locale = $entityManager
                ->getRepository(DataLocale::class)
                ->findOneBy([
                    'isDefault' => true,
                ]);
        }

        $path = "/{$path}";
        $page = $entityManager->getRepository(Page::class)->findOneBy([
            'path' => $path,
            'locale' => $locale,
        ]);
        if ($page === null) {
            throw new NotFoundHttpException();
        }

        $layoutData = $entityManager
            ->getRepository(LayoutData::class)
            ->findOneBy([
                'name' => $page->getLayoutName(),
                'theme' => $cms->getThemeName(),
                'locale' => $locale,
            ]);

        $layoutPath = $page->getLayoutName()
            ? $cms->getLayoutTemplatePath($page->getLayoutName())
            : null;

        return $this->render('client/index.twig', [
            'layoutPath' => $layoutPath,
            'layout' => $layoutData?->getData(),
            'page' => $page,
        ]);
    }

    #[
        Route(
            '/__actions/{name}/{method}',
            name: 'client_action',
            requirements: ['name' => '\w+', 'method' => '\w+'],
        ),
    ]
    public function action(
        string $name,
        string $method,
        Cms $cms,
        Request $request,
        EntityManagerInterface $entityManager,
        Validation $validation,
        SerializerInterface $serializer,
    ): Response {
        $serviceId = sprintf(
            'Themes\\%s\\Actions\\%sActionController',
            $cms->getThemeName(),
            $name,
        );

        if (!$this->fullContainer->has($serviceId)) {
            throw $this->createNotFoundException(
                "Action service not found: $serviceId",
            );
        }

        $controller = $this->fullContainer->get($serviceId);

        $resolvedArguments = $this->argumentResolver->getArguments($request, [
            $controller,
            $method,
        ]);

        return call_user_func_array([$controller, $method], $resolvedArguments);
    }

    #[
        Route(
            '/__static/{path}',
            name: 'client_static',
            requirements: ['path' => '.+'],
        ),
    ]
    public function static(string $path, Cms $cms): BinaryFileResponse
    {
        $staticRoot = $cms->getStaticDir();
        $filePath = "{$staticRoot}/{$path}";

        if (!file_exists($filePath)) {
            throw $this->createNotFoundException();
        }

        $response = new BinaryFileResponse($filePath);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_INLINE);

        return $response;
    }
}
