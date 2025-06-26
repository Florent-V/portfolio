<?php

declare(strict_types=1);

namespace App\Controller;

use App\Form\ContactFormType;
use App\Repository\AboutMeRepository;
use App\Repository\EducationRepository;
use App\Repository\ExperienceRepository;
use App\Repository\HobbyRepository;
use App\Repository\ProjectRepository;
use App\Repository\SkillCategoryRepository; // Added
use App\Repository\SoftSkillRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home', methods: ['GET', 'POST'])]
    public function index(
        Request $request,
        MailerInterface $mailer,
        AboutMeRepository $aboutMeRepository,
        ProjectRepository $projectRepository,
        SkillCategoryRepository $skillCategoryRepository,
        SoftSkillRepository $softSkillRepository, // Added
        EducationRepository $educationRepository,
        ExperienceRepository $experienceRepository,
        HobbyRepository $hobbyRepository,
    ): Response {
        $aboutMe         = $aboutMeRepository->findOneBy([]);
        $projects        = $projectRepository->findBy(['published' => true], ['startDate' => 'DESC'], 6);
        $skillCategories = $skillCategoryRepository->findBy([], ['displayOrder' => 'ASC', 'name' => 'ASC']);
        $softSkills      = $softSkillRepository->findAllOrdered(); // Added
        $educations      = $educationRepository->findBy([], ['startDate' => 'DESC']);
        $experiences     = $experienceRepository->findBy([], ['startDate' => 'DESC']);
        $hobbies         = $hobbyRepository->findBy([], ['name' => 'ASC']);

        $contactForm = $this->createForm(ContactFormType::class);
        $contactForm->handleRequest($request);

        if ($contactForm->isSubmitted() && $contactForm->isValid()) {
            $data = $contactForm->getData();

            $email = (new Email())
                ->from($data['email']) // Sender's email
                // Your admin email (configure in services.yaml or .env)
                ->to($this->getParameter('app.admin_email'))
                ->subject('Nouveau message de contact Portfolio: ' . $data['subject'])
                ->html($this->renderView('emails/contact_email.html.twig', [
                    'name'         => $data['name'],
                    'sender_email' => $data['email'],
                    'subject'      => $data['subject'],
                    'message'      => $data['message'],
                ]));

            try {
                $mailer->send($email);
                $this->addFlash(
                    'success',
                    'Votre message a bien été envoyé ! Je vous répondrai dès que possible.'
                );
            } catch (\Symfony\Component\Mailer\Exception\TransportExceptionInterface $e) {
                $this->addFlash(
                    'error',
                    'Une erreur est survenue lors de l\'envoi du message. ' .
                    'Veuillez réessayer plus tard. Détail: ' . $e->getMessage()
                );
                // Log the error for debugging:
                // $this->container->get('logger')->error('Contact form mailer error: ' . $e->getMessage());
            }

            // Redirect to avoid form resubmission on refresh
            return $this->redirectToRoute('app_home', ['_fragment' => 'contact']);
        }

        return $this->render('home/index.html.twig', [
            'aboutMe'         => $aboutMe,
            'projects'        => $projects,
            'skillCategories' => $skillCategories,
            'softSkills'      => $softSkills, // Added
            'educations'      => $educations,
            'experiences'     => $experiences,
            'hobbies'         => $hobbies,
            'contactForm'     => $contactForm->createView(),
        ]);
    }

    #[Route('/theme', name: 'app_theme')]
    public function theme(): Response
    {
        return $this->render('home/theme.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }
}
