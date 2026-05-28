<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Article;
use App\Entity\User;
use App\Repository\TagRepository;
use App\Service\Admin\ArticleFieldsConfigurationService;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Asset;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * @extends AbstractCrudController<Article>
 */
class ArticleCrudController extends AbstractCrudController
{
    use AdminCrudControllerTrait;

    public function __construct(
        private readonly AdminUrlGenerator $adminUrlGenerator,
        private readonly ArticleFieldsConfigurationService $fieldsService,
        private readonly Security $security,
        private readonly TagRepository $tagRepository,
        private readonly EntityManagerInterface $em,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return Article::class;
    }

    #[\Override]
    public function configureCrud(Crud $crud): Crud
    {
        return $this->configureCommonCrud($crud, 'Article', 'Articles')
            ->setSearchFields(['title', 'slug', 'author.email', 'tags.name'])
            ->setDefaultSort(['publishedAt' => 'DESC', 'createdAt' => 'DESC'])
            ->setHelp(
                Crud::PAGE_INDEX,
                'Gérez vos articles de blog et leur publication. ' .
                'Associez-leur des tags pour améliorer la navigation.'
            );
    }

    #[\Override]
    public function configureAssets(Assets $assets): Assets
    {
        return $assets
            ->addCssFile(Asset::fromEasyAdminAssetPackage('field-text-editor.css')->onlyOnForms())
            ->addJsFile(Asset::fromEasyAdminAssetPackage('field-text-editor.js')->onlyOnForms());
    }

    #[\Override]
    public function configureFields(string $pageName): iterable
    {
        return $this->fieldsService->getFieldsForPage($pageName, $this->getContext());
    }

    /**
     * @return FormBuilderInterface<mixed>
     */
    #[\Override]
    public function createEditFormBuilder(
        EntityDto $entityDto,
        KeyValueStore $formOptions,
        AdminContext $context,
    ): FormBuilderInterface {
        $builder = parent::createEditFormBuilder($entityDto, $formOptions, $context);
        $this->addTagsPreSubmitListener($builder);

        return $builder;
    }

    /**
     * @return FormBuilderInterface<mixed>
     */
    #[\Override]
    public function createNewFormBuilder(
        EntityDto $entityDto,
        KeyValueStore $formOptions,
        AdminContext $context,
    ): FormBuilderInterface {
        $builder = parent::createNewFormBuilder($entityDto, $formOptions, $context);
        $this->addTagsPreSubmitListener($builder);

        return $builder;
    }

    /**
     * @param FormBuilderInterface<mixed> $builder
     */
    private function addTagsPreSubmitListener(FormBuilderInterface $builder): void
    {
        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event): void {
            $data = $event->getData();
            if (!isset($data['tags']) || !is_array($data['tags'])) {
                return;
            }

            $resolvedIds = [];
            foreach ($data['tags'] as $value) {
                if (ctype_digit((string) $value)) {
                    $resolvedIds[] = $value;
                    continue;
                }

                $tag = $this->tagRepository->findOneByNameOrCreate(trim((string) $value));
                $this->em->flush();
                $resolvedIds[] = (string) $tag->getId();
            }

            $data['tags'] = $resolvedIds;
            $event->setData($data);
        });
    }

    public function createEntity(string $entityFqcn): Article
    {
        $article     = new Article();
        $currentUser = $this->security->getUser();
        if ($currentUser instanceof User) {
            $article->setAuthor($currentUser);
        }
        $article->setIsPublished(false);

        return $article;
    }

    #[\Override]
    public function configureActions(Actions $actions): Actions
    {
        $adminUrlGenerator = $this->adminUrlGenerator;

        $viewContentElements = Action::new('viewContentElements', 'Blocs de contenu', 'fa fa-list')
            ->linkToUrl(static function (Article $entity) use ($adminUrlGenerator): string {
                return $adminUrlGenerator
                    ->setController(ArticleContentCrudController::class)
                    ->setAction('index')
                    ->set('filters[article][comparison]', '=')
                    ->set('filters[article][value]', (string) $entity->getId())
                    ->generateUrl();
            })
            ->setHtmlAttributes(['title' => 'Voir les blocs de contenu de cet article']);

        return $this->configureSoftDeleteActions(
            $this->configureCommonActions($actions)
        )
            ->add(Crud::PAGE_INDEX, $viewContentElements)
            ->add(
                Crud::PAGE_INDEX,
                $this->buildDuplicateAction()
            )
            ->update(
                Crud::PAGE_INDEX,
                Action::NEW,
                fn (Action $a) => $a
                    ->setIcon('fa fa-newspaper')
                    ->setLabel('Nouvel Article')
                    ->setCssClass('btn btn-success')
            );
    }

    #[\Override]
    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('title')
            ->add('slug')
            ->add('author')
            ->add('tags')
            ->add('isPublished')
            ->add('publishedAt')
            ->add('createdAt')
            ->add('updatedAt');
    }

    protected function getAdminUrlGenerator(): AdminUrlGenerator
    {
        return $this->adminUrlGenerator;
    }
}
