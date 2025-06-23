<?php

declare(strict_types=1);

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

readonly class AuthenticationEntryPoint implements AuthenticationEntryPointInterface
{
    public function __construct(
        private RouterInterface $router,
    ) {
    }

    public function start(Request $request, ?AuthenticationException $authException = null): RedirectResponse
    {
        // add a custom flash message and redirect to the login page
        // @phpstan-ignore method.notFound
        $request->getSession()->getFlashBag()->add('warning', 'Vous devez d\'abord vous connecter
        pour accéder à cette page');

        return new RedirectResponse($this->router->generate('app_login'));
    }
}
