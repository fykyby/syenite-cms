<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\Cms;
use App\Service\Validation;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ArgumentResolverInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

final class ActionController extends AbstractController
{
    public function __construct(
        private ContainerInterface $fullContainer,
        private ArgumentResolverInterface $argumentResolver,
    ) {}

    #[
        Route(
            '/__actions/{name}/{method}',
            name: 'app_action',
            requirements: ['name' => '\w+', 'method' => '\w+'],
            methods: ['GET', 'POST'],
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
}
