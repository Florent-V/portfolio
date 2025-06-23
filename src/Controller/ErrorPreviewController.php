<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ErrorPreviewController extends AbstractController
{
    #[Route('/error/400', name: 'error_400')]
    public function error400(): Response
    {
        return $this->render('bundles/TwigBundle/Exception/error400.html.twig');
    }

    #[Route('/error/401', name: 'error_401')]
    public function error401(): Response
    {
        return $this->render('bundles/TwigBundle/Exception/error401.html.twig');
    }

    #[Route('/error/403', name: 'error_403')]
    public function error403(): Response
    {
        return $this->render('bundles/TwigBundle/Exception/error403.html.twig');
    }

    #[Route('/error/404', name: 'error_404')]
    public function error404(): Response
    {
        return $this->render('bundles/TwigBundle/Exception/error404.html.twig');
    }

    #[Route('/error/418', name: 'error_418')]
    public function error418(): Response
    {
        return $this->render('bundles/TwigBundle/Exception/error418.html.twig');
    }

    #[Route('/error/500', name: 'error_500')]
    public function error500(): Response
    {
        return $this->render('bundles/TwigBundle/Exception/error500.html.twig');
    }

    #[Route('/error/502', name: 'error_502')]
    public function error502(): Response
    {
        return $this->render('bundles/TwigBundle/Exception/error502.html.twig');
    }

    #[Route('/error/503', name: 'error_503')]
    public function error503(): Response
    {
        return $this->render('bundles/TwigBundle/Exception/error503.html.twig');
    }

    #[Route('/error/504', name: 'error_504')]
    public function error504(): Response
    {
        return $this->render('bundles/TwigBundle/Exception/error504.html.twig');
    }

    #[Route('/error/other', name: 'error_other')]
    public function errorOther(): Response
    {
        return $this->render('bundles/TwigBundle/Exception/error.html.twig');
    }
}
