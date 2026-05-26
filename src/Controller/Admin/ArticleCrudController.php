<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Article;
use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use App\Form\ArticleContentFormType;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Bundle\SecurityBundle\Security;
use Vich\UploaderBundle\Form\Type\VichImageType;

class ArticleCrudController extends AbstractCrudController
{
    public function __construct(private readonly Security $security)
    {
    }

    public static function getEntityFqcn(): string
    {
        return Article::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Article')
            ->setEntityLabelInPlural('Articles')
            ->setPageTitle(Crud::PAGE_INDEX, 'Gestion des articles')
            ->setPageTitle(Crud::PAGE_EDIT, 'Modifier l\'article')
            ->setPageTitle(Crud::PAGE_NEW, 'Créer un article')
            ->setPageTitle(Crud::PAGE_DETAIL, 'Détail de l\'article')
            ->setSearchFields(['title', 'content', 'slug', 'author.email', 'technologies.name'])
            ->setDefaultSort(['publishedAt' => 'DESC', 'createdAt' => 'DESC'])
            ->setPaginatorPageSize(25)
            ->showEntityActionsInlined()
            ->setAutofocusSearch()
            ->setTimezone('Europe/Paris')
            ->setHelp(Crud::PAGE_INDEX, 'Gérez vos articles de blog et leur publication.'
                . ' Associez-les à des technologies pour améliorer la navigation.');
    }

    public function configureFields(string $pageName): iterable
    {
        return match ($pageName) {
            Crud::PAGE_INDEX  => $this->getIndexFields(),
            Crud::PAGE_DETAIL => $this->getDetailFields(),
            Crud::PAGE_NEW, Crud::PAGE_EDIT => $this->getFormFields(),
            default => [],
        };
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

    /** @return FieldInterface[] */
    private function getIndexFields(): array
    {
        return [
            $this->createMainImageField()->setLabel('Image'),
            $this->createTitleField(),
            $this->createAuthorField(),
            $this->createTechnologiesField(),
            $this->createIsPublishedField(),
            $this->createPublishedAtField(),
            $this->createCreatedAtField(),
        ];
    }

    /** @return FieldInterface[] */
    private function getDetailFields(): array
    {
        return [
            $this->createMainImageField()->setLabel('Image Principale'),
            $this->createTitleField(),
            $this->createSlugField(),
            $this->createContentDetailField(),
            $this->createAuthorField(),
            $this->createTechnologiesField(),
            $this->createIsPublishedField(),
            $this->createPublishedAtField(),
            $this->createCreatedAtField(),
            $this->createUpdatedAtField(),
            $this->createCreatedByField(),
            $this->createUpdatedByField(),
        ];
    }

    /** @return FieldInterface[] */
    private function getFormFields(): array
    {
        return [
            $this->createTitleField(),
            $this->createSlugField(),
            $this->createMainImageUploadField(),
            $this->createAuthorField(),
            $this->createTechnologiesField(),
            $this->createIsPublishedField(),
            $this->createPublishedAtField(),
            CollectionField::new('contentElements', 'Contenu de l\'article')
                ->setEntryType(ArticleContentFormType::class)
                ->setFormTypeOptions(['by_reference' => false])
                ->onlyOnForms(),
        ];
    }

    private function createTitleField(): TextField
    {
        return TextField::new('title', 'Titre')
            ->setHelp('Titre de l\'article qui sera affiché en en-tête.');
    }

    private function createSlugField(): SlugField
    {
        return SlugField::new('slug', 'Slug')
            ->setTargetFieldName('title')
            ->setHelp('URL-friendly version du titre. Généré automatiquement si laissé vide.');
    }


    private function createAuthorField(): AssociationField
    {
        return AssociationField::new('author', 'Auteur')
            ->setRequired(true)
            ->setHelp('Auteur de l\'article. Défini automatiquement lors de la création.');
    }

    private function createTechnologiesField(): AssociationField
    {
        return AssociationField::new('technologies', 'Technologies')
            ->setFormTypeOptions(['by_reference' => false])
            ->setHelp('Technologies associées à cet article pour faciliter la recherche et la navigation.');
    }

    private function createMainImageField(): ImageField
    {
        return ImageField::new('mainImageName', 'Image')
            ->setBasePath('/uploads/images/articles')
            ->setHelp('Image principale affichée avec l\'article.');
    }

    private function createMainImageUploadField(): TextareaField
    {
        return TextareaField::new('mainImageFile', 'Image Principale')
            ->setFormType(VichImageType::class)
            ->setHelp('Image principale de l\'article (JPEG, PNG, WEBP). Max 5MB.')
            ->setRequired(false);
    }

    private function createIsPublishedField(): BooleanField
    {
        return BooleanField::new('isPublished', 'Publié')
            ->setHelp('Définit si l\'article est visible publiquement sur le site.');
    }

    private function createPublishedAtField(): DateTimeField
    {
        return DateTimeField::new('publishedAt', 'Date de publication')
            ->setFormat('dd/MM/yyyy HH:mm')
            ->setHelp('Date et heure de publication de l\'article.');
    }

    private function createCreatedAtField(): DateTimeField
    {
        return DateTimeField::new('createdAt', 'Créé le')
            ->setFormat('dd/MM/yyyy HH:mm');
    }

    private function createUpdatedAtField(): DateTimeField
    {
        return DateTimeField::new('updatedAt', 'Mis à jour le')
            ->setFormat('dd/MM/yyyy HH:mm');
    }

    private function createCreatedByField(): AssociationField
    {
        return AssociationField::new('createdBy', 'Créé par');
    }

    private function createUpdatedByField(): AssociationField
    {
        return AssociationField::new('updatedBy', 'Mis à jour par');
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->update(Crud::PAGE_INDEX, Action::NEW, fn (Action $action) => $action
                ->setIcon('fa fa-newspaper')
                ->setLabel('Nouvel Article')
                ->setCssClass('btn btn-success'))
            ->update(Crud::PAGE_INDEX, Action::EDIT, fn (Action $action) => $action
                ->setIcon('fa fa-edit'))
            ->update(Crud::PAGE_INDEX, Action::DELETE, fn (Action $action) => $action
                ->setIcon('fa fa-trash'))
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(Crud::PAGE_INDEX, Action::DETAIL, fn (Action $action) => $action
                ->setIcon('fa fa-eye'));
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('title')
            ->add('slug')
            ->add('author')
            ->add('technologies')
            ->add('isPublished')
            ->add('publishedAt')
            ->add('createdAt')
            ->add('updatedAt');
    }
}
