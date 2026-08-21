<?php

declare(strict_types=1);

namespace App\Controller;

use App\Form\ContactFormType;
use App\Service\ContactFormHandler;
use App\Service\HomePageDataService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    public function __construct(
        private readonly HomePageDataService $homePageDataService,
    ) {
    }

    #[Route('/', name: 'app_home', methods: ['GET', 'POST'])]
    public function index(
        Request $request,
        ContactFormHandler $contactFormHandler,
    ): Response {
        $pageData = $this->homePageDataService->getDataForHomepage();

        $contactForm = $this->createForm(ContactFormType::class);
        $contactForm->handleRequest($request);

        if ($contactForm->isSubmitted() && $contactForm->isValid()) {
            $this->sendContactMessage($contactFormHandler, $contactForm->getData());

            return $this->redirectToRoute('app_home', ['_fragment' => 'contact']);
        }

        if ($contactForm->isSubmitted()) {
            $this->addFlash(
                'error',
                'Veuillez corriger les erreurs dans le formulaire.'
            );
        }

        $response = new Response(
            null,
            $contactForm->isSubmitted() ? Response::HTTP_UNPROCESSABLE_ENTITY : Response::HTTP_OK
        );

        return $this->render(
            'home/index.html.twig',
            array_merge($pageData, ['contactForm' => $contactForm->createView()]),
            $response
        );
    }

    #[Route('/theme', name: 'app_theme')]
    public function theme(): Response
    {
        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @param array{name: string, email: string, subject: string, message: string} $formData
     */
    private function sendContactMessage(ContactFormHandler $contactFormHandler, array $formData): void
    {
        if ($contactFormHandler->send($formData)) {
            $this->addFlash(
                'success',
                'Votre message a bien été envoyé ! Je vous répondrai dès que possible.'
            );

            return;
        }

        $this->addFlash(
            'error',
            'Une erreur est survenue lors de l\'envoi de votre message. Veuillez réessayer.'
        );
    }
}
