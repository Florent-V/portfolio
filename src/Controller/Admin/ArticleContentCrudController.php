<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\ArticleContent;
use App\Service\Admin\ArticleContentFieldsConfigurationService;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

/**
 * @extends AbstractCrudController<ArticleContent>
 */
class ArticleContentCrudController extends AbstractCrudController
{
    use AdminCrudControllerTrait;

    public function __construct(
        private readonly AdminUrlGenerator $adminUrlGenerator,
        private readonly ArticleContentFieldsConfigurationService $fieldsService,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return ArticleContent::class;
    }

    #[\Override]
    public function configureCrud(Crud $crud): Crud
    {
        return $this->configureCommonCrud($crud, 'Bloc de contenu', 'Blocs de contenu')
            ->setSearchFields(['article.title', 'language', 'altText'])
            ->setDefaultSort(['article' => 'ASC', 'displayOrder' => 'ASC'])
            ->addFormTheme('@VichUploader/Form/fields.html.twig');
    }

    #[\Override]
    public function configureActions(Actions $actions): Actions
    {
        $adminUrlGenerator = $this->adminUrlGenerator;

        $viewParentArticle = Action::new('viewParentArticle', 'Voir l\'article', 'fa fa-newspaper')
            ->linkToUrl(static function (ArticleContent $entity) use ($adminUrlGenerator): string {
                return $adminUrlGenerator
                    ->setController(ArticleCrudController::class)
                    ->setAction('detail')
                    ->setEntityId($entity->getArticle()?->getId())
                    ->generateUrl();
            })
            ->addCssClass('btn btn-info')
            ->setHtmlAttributes(['title' => 'Voir l\'article parent']);

        return $this->configureCommonActions($actions)
            ->add(Crud::PAGE_INDEX, $viewParentArticle)
            ->add(Crud::PAGE_DETAIL, $viewParentArticle);
    }

    #[\Override]
    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('article', 'Article'));
    }

    #[\Override]
    public function configureFields(string $pageName): iterable
    {
        return $this->fieldsService->getFieldsForPage($pageName, $this->getContext());
    }

    protected function getAdminUrlGenerator(): AdminUrlGenerator
    {
        return $this->adminUrlGenerator;
    }
}
