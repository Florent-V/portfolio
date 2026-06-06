<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\AI\ArticleSummaryService;
use App\AI\Enum\AiProvider;
use App\Enum\Role;
use App\Form\Admin\ArticleSummarizerFormType;
use App\Repository\ArticleRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(
    path: '/admin/article/{id}/summarize',
    name: 'admin_article_summarize',
    methods: ['GET', 'POST']
)]
#[IsGranted(Role::ADMIN->value)]
class ArticleSummarizerController extends AbstractController
{
    public function __invoke(
        int $id,
        Request $request,
        ArticleRepository $articleRepository,
        ArticleSummaryService $summaryService,
        AdminUrlGenerator $adminUrlGenerator,
    ): Response {
        set_time_limit(0);
        ignore_user_abort(true);

        $article = $articleRepository->find($id);
        if (null === $article) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(ArticleSummarizerFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var array{provider: AiProvider} $data */
            $data = $form->getData();

            $request->getSession()->save();

            try {
                $summaryService->generateAndAttach($article, $data['provider']);
                $this->addFlash('success', 'Résumé IA généré avec succès.');
            } catch (\Throwable $e) {
                $this->addFlash('danger', 'Erreur : ' . $e->getMessage());
            }

            return $this->redirect(
                $adminUrlGenerator
                    ->setController(ArticleCrudController::class)
                    ->setAction(Action::EDIT)
                    ->setEntityId($article->getId())
                    ->generateUrl()
            );
        }

        return $this->render('admin/article_summarizer/index.html.twig', [
            'form'    => $form,
            'article' => $article,
        ]);
    }
}
