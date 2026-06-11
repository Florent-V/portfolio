<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(
    path: '/robots.txt',
    name: 'app_robots',
    methods: ['GET']
)]
final class RobotsController extends AbstractController
{
    public function __invoke(): Response
    {
        $response = new Response(
            $this->renderView('seo/robots.txt.twig'),
            Response::HTTP_OK,
            ['Content-Type' => 'text/plain'],
        );

        $response->setPublic();
        $response->setMaxAge(86400);

        return $response;
    }
}
