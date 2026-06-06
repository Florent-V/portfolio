<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\AI\ArticleResponseMapper;
use App\AI\ArticleResponseParser;
use App\AI\Exception\ArticleGenerationException;
use App\Entity\User;
use App\Enum\Role;
use App\Form\Admin\ArticleJsonImportFormType;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(
    path: '/admin/import-article-json',
    name: 'admin_article_json_import',
    methods: ['GET', 'POST']
)]
#[IsGranted(Role::ADMIN->value)]
class ArticleJsonImportController extends AbstractController
{
    public function __invoke(
        Request $request,
        ArticleResponseParser $responseParser,
        ArticleResponseMapper $responseMapper,
        EntityManagerInterface $entityManager,
        AdminUrlGenerator $adminUrlGenerator,
    ): Response {
        $form = $this->createForm(ArticleJsonImportFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $file */
            $file = $form->get('jsonFile')->getData();

            /** @var User $user */
            $user = $this->getUser();

            try {
                $rawJson       = (string) file_get_contents($file->getPathname());
                $generatedData = $responseParser->parse($rawJson);
                $article       = $responseMapper->map($generatedData, $user);
                $entityManager->persist($article);
                $entityManager->flush();

                $this->addFlash(
                    'success',
                    \sprintf(
                        'Article "%s" importé et sauvegardé en brouillon !',
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
                $this->addFlash('danger', \sprintf("Erreur d'import : %s", $e->getMessage()));
            }
        }

        return $this->render('admin/article_importer/index.html.twig', [
            'form' => $form,
        ]);
    }
}
