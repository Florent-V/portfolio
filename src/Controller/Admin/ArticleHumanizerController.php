<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\AI\ArticleHumanizerService;
use App\AI\DTO\ArticleHumanizeRequest;
use App\AI\Enum\AiProvider;
use App\AI\Exception\ArticleGenerationException;
use App\Entity\User;
use App\Enum\ArticleContentFormat;
use App\Enum\Role;
use App\Form\Admin\ArticleHumanizerFormType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(
    path: '/admin/humanize-article',
    name: 'admin_article_humanizer',
    methods: ['GET', 'POST']
)]
#[IsGranted(Role::ADMIN->value)]
class ArticleHumanizerController extends AbstractController
{
    public function __invoke(
        Request $request,
        ArticleHumanizerService $humanizerService,
        AdminUrlGenerator $adminUrlGenerator,
    ): Response {
        set_time_limit(0);
        ignore_user_abort(true);

        $form = $this->createForm(ArticleHumanizerFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var User $user */
            $user = $this->getUser();

            $request->getSession()->save();

            $humanizeRequest = new ArticleHumanizeRequest(
                rawText: $form->get('rawText')->getData(),
                language: $form->get('language')->getData(),
                author: $user,
                provider: $form->get('provider')->getData() ?? AiProvider::OPENROUTER,
                format: $form->get('format')->getData()     ?? ArticleContentFormat::HTML,
            );

            try {
                $article = $humanizerService->humanize($humanizeRequest);

                $this->addFlash(
                    'success',
                    \sprintf(
                        'Article "%s" humanisé et sauvegardé en brouillon !',
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
                $this->addFlash('danger', \sprintf('Erreur : %s', $e->getMessage()));
            }
        }

        return $this->render('admin/article_humanizer/index.html.twig', [
            'form' => $form,
        ]);
    }
}
