<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

class LoginRedirectSubscriber implements EventSubscriberInterface
{
    private UrlGeneratorInterface $urlGenerator;
    private AuthorizationCheckerInterface $authorizationChecker;

    public function __construct(
        UrlGeneratorInterface $urlGenerator,
        AuthorizationCheckerInterface $authorizationChecker,
    ) {
        $this->urlGenerator         = $urlGenerator;
        $this->authorizationChecker = $authorizationChecker;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            LoginSuccessEvent::class => 'onLoginSuccess',
        ];
    }

    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
        if ($this->authorizationChecker->isGranted('ROLE_ADMIN')) {
            $response = new RedirectResponse($this->urlGenerator->generate('admin'));
            $event->setResponse($response);
        }
        $response = new RedirectResponse($this->urlGenerator->generate('app_home'));

        $event->setResponse($response);
    }
}
