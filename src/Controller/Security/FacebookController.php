<?php

declare(strict_types=1);

namespace App\Controller\Security;

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class FacebookController extends AbstractController
{
    #[Route('/connect/facebook', name: 'connect_facebook_start')]
    public function connectAction(ClientRegistry $clientRegistry): RedirectResponse
    {
        // will redirect to Facebook!
        return $clientRegistry
            ->getClient('facebook') // key used in config/packages/knpu_oauth2_client.yaml
            ->redirect([
                'public_profile', // the scopes you want to access
            ], []);
    }

    #[Route('/connect/facebook/check', name: 'connect_facebook_check')]
    public function connectCheckAction(Request $request, ClientRegistry $clientRegistry): void
    {
        // this method is intended to be blank - it will never be called
    }
}
