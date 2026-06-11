<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\AI\ArticleGeneratorService;
use App\AI\DTO\ArticleGenerationRequest;
use App\AI\Enum\AiProvider;
use App\AI\Exception\ArticleGenerationException;
use App\Entity\User;
use App\Enum\Role;
use App\Form\Admin\ArticleGeneratorFormType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(
    path: '/admin/generate-article',
    name: 'admin_article_generator',
    methods: ['GET', 'POST']
)]
#[IsGranted(Role::ADMIN->value)]
class ArticleGeneratorController extends AbstractController
{
    public function __invoke(
        Request $request,
        ArticleGeneratorService $generatorService,
        AdminUrlGenerator $adminUrlGenerator,
    ): Response {
        set_time_limit(0);
        ignore_user_abort(true);

        $form = $this->createForm(ArticleGeneratorFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var array{
             *     provider: ?AiProvider,
             *     topic: string,
             *     language: string,
             *     useWebSearch: ?bool,
             *     extraInstructions: ?string
             * } $data
             */
            $data = $form->getData();

            /** @var User $user */
            $user = $this->getUser();

            $provider = $data['provider'] ?? AiProvider::OPENROUTER;

            $request->getSession()->save();

            $generationRequest = new ArticleGenerationRequest(
                topic: $data['topic'],
                language: $data['language'],
                useWebSearch: $provider->supportsWebSearch() && (bool) ($data['useWebSearch'] ?? false),
                extraInstructions: $data['extraInstructions'] ?? null,
                author: $user,
                provider: $provider,
            );

            try {
                $article = $generatorService->generate($generationRequest);

                $this->addFlash(
                    'success',
                    \sprintf(
                        'Article "%s" généré et sauvegardé en brouillon !',
                        $article->getTitle()
                    ),
                );

                return $this->redirect(
                    $adminUrlGenerator
                        ->setController(ArticleCrudController::class)
                        ->setAction(Action::EDIT)
                        ->setEntityId($article->getId())
                        ->generateUrl()
                );
            } catch (ArticleGenerationException $e) {
                $this->addFlash('danger', \sprintf('Erreur de génération : %s', $e->getMessage()));

                return $this->render('admin/article_generator/index.html.twig', [
                    'form' => $form,
                ], new Response(status: Response::HTTP_UNPROCESSABLE_ENTITY));
            }
        }

        return $this->render('admin/article_generator/index.html.twig', [
            'form' => $form,
        ]);
    }
}
