<?php

declare(strict_types=1);

namespace App\Controller;

use App\Form\ContactFormType;
use App\Service\HomePageDataService;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    public function __construct(
        private readonly HomePageDataService $homePageDataService,
    ) {
    }

    #[Route('/', name: 'app_home', methods: ['GET', 'POST'])]
    public function index(Request $request, MailerInterface $mailer): Response
    {
        $pageData = $this->homePageDataService->getDataForHomepage();

        $contactForm = $this->createForm(ContactFormType::class);
        $contactForm->handleRequest($request);

        if ($contactForm->isSubmitted() && $contactForm->isValid()) {
            $formData = $contactForm->getData();

            $email = (new TemplatedEmail())
                ->replyTo($formData['email'])
                ->to($this->getParameter('app.admin_email'))
                ->subject('Nouveau message de contact Portfolio: ' . $formData['subject'])
                ->htmlTemplate('emails/contact_email.html.twig')
                ->context([
                    'name'         => $formData['name'],
                    'sender_email' => $formData['email'],
                    'subject'      => $formData['subject'],
                    'message'      => $formData['message'],
                ]);

            try {
                $mailer->send($email);
                $this->addFlash(
                    'success',
                    'Votre message a bien été envoyé ! Je vous répondrai dès que possible.'
                );
            } catch (TransportExceptionInterface) {
                $this->addFlash(
                    'error',
                    'Une erreur est survenue lors de l\'envoi de votre message. Veuillez réessayer.'
                );
            }

            return $this->redirectToRoute('app_home', ['_fragment' => 'contact']);
        }

        return $this->render('home/index.html.twig', array_merge($pageData, [
            'contactForm' => $contactForm->createView(),
        ]));
    }

    #[Route('/theme', name: 'app_theme')]
    public function theme(): Response
    {
        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
