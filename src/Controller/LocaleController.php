<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class LocaleController extends AbstractController
{
    #[Route('/switch-locale/{locale}', name: 'app_switch_locale')]
    public function switchLocale(Request $request, string $locale): Response
    {
        // Check if the locale is supported (fr, en)
        if (!in_array($locale, ['fr', 'en'])) {
            throw $this->createNotFoundException('Locale not supported');
        }

        $request->getSession()->set('_locale', $locale);

        // Redirect to the previous page or homepage
        $referer = $request->headers->get('referer');
        if ($referer) {
            // Basic check to avoid redirecting to external sites if referer is manipulated
            // And avoid redirecting to the switch_locale route itself
            $host = $request->getHost();
            if (str_starts_with($referer, 'http://' . $host) || str_starts_with($referer, 'https://' . $host)) {
                if (!str_contains($referer, '/switch-locale/')) {
                    return $this->redirect($referer);
                }
            }
        }

        return $this->redirectToRoute('app_home');
    }
}
