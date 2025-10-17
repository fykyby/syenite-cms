<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\DataLocale;
use App\Entity\Page;
use App\Repository\DataLocaleRepository;
use App\Repository\PageRepository;
use App\Service\Cms;
use App\Service\MailerService;
use App\Service\Validation;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpKernel\Controller\ArgumentResolverInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Cache\CacheInterface;

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
        CacheInterface $cache,
    ): Response {
        $requestDomain = $request->getHost();
        $path = "/{$path}";

        /** @var DataLocaleRepository $localeRepository */
        $localeRepository = $entityManager->getRepository(DataLocale::class);
        $locale = $cache->get(
            "app.locale.{$requestDomain}",
            fn() => $localeRepository->findByDomainOrDefault($requestDomain),
        );

        if ($locale === null) {
            throw new NotFoundHttpException('No locale found');
        }

        /** @var PageRepository $pageRepository */
        $pageRepository = $entityManager->getRepository(Page::class);
        $page = $pageRepository->findOneByPathAndLocaleWithLayoutData(
            $path,
            $locale,
        );
        if ($page === null) {
            throw new NotFoundHttpException();
        }

        $layoutName = $page->getLayoutData()?->getName() ?? null;
        $layoutPath = $layoutName
            ? $cms->getLayoutTemplatePath($layoutName)
            : null;

        return $this->render('client/index.twig', [
            'layoutPath' => $layoutPath,
            'layout' => $page->getLayoutData()?->getData() ?? [],
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
        Request $request,
        EntityManagerInterface $entityManager,
        Cms $cms,
        MailerService $mailer,
        Validation $validation,
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
