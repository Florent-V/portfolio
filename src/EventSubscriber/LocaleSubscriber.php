<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Sets the locale on the request based on the session.
 */
class LocaleSubscriber implements EventSubscriberInterface
{
    private string $defaultLocale;

    public function __construct(string $defaultLocale = 'fr')
    {
        $this->defaultLocale = $defaultLocale;
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        if (!$request->hasPreviousSession()) {
            return;
        }

        // If a locale is set as a routing parameter, use it and save it to the session
        $localeInRoute = $request->attributes->get('_locale');
        if (null !== $localeInRoute) {
            $request->getSession()->set('_locale', $localeInRoute);

            return;
        }

        // If no explicit locale is set, use the one from the session
        $request->setLocale($request->getSession()->get('_locale', $this->defaultLocale));
    }

    public static function getSubscribedEvents(): array
    {
        return [
            // Must be registered before (i.e. with a higher priority than) the default Locale listener
            KernelEvents::REQUEST => [['onKernelRequest', 20]],
        ];
    }
}
