<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Article;
use App\Entity\User;
use App\Repository\TagRepository;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Asset;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Vich\UploaderBundle\Form\Type\VichImageType;

class ArticleCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly Security $security,
        private readonly TagRepository $tagRepository,
        private readonly EntityManagerInterface $em,
    ) {
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
            ->setSearchFields(['title', 'slug', 'author.email', 'tags.name'])
            ->setDefaultSort(['publishedAt' => 'DESC', 'createdAt' => 'DESC'])
            ->setPaginatorPageSize(25)
            ->showEntityActionsInlined()
            ->setAutofocusSearch()
            ->setTimezone('Europe/Paris')
            ->setHelp(Crud::PAGE_INDEX, 'Gérez vos articles de blog et leur publication.'
                . ' Associez-leur des tags pour améliorer la navigation.');
    }

    public function configureAssets(Assets $assets): Assets
    {
        return $assets
            ->addCssFile(Asset::fromEasyAdminAssetPackage('field-text-editor.css')->onlyOnForms())
            ->addJsFile(Asset::fromEasyAdminAssetPackage('field-text-editor.js')->onlyOnForms());
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

    /** @return FormBuilderInterface<mixed> */
    public function createEditFormBuilder(
        EntityDto $entityDto,
        KeyValueStore $formOptions,
        \EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext $context,
    ): FormBuilderInterface {
        $builder = parent::createEditFormBuilder($entityDto, $formOptions, $context);
        $this->addTagsPreSubmitListener($builder);

        return $builder;
    }

    /** @return FormBuilderInterface<mixed> */
    public function createNewFormBuilder(
        EntityDto $entityDto,
        KeyValueStore $formOptions,
        \EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext $context,
    ): FormBuilderInterface {
        $builder = parent::createNewFormBuilder($entityDto, $formOptions, $context);
        $this->addTagsPreSubmitListener($builder);

        return $builder;
    }

    /** @param FormBuilderInterface<mixed> $builder */
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

                $tag = $this->tagRepository->findOneByNameOrCreate(trim($value));
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

    /** @return FieldInterface[] */
    private function getIndexFields(): array
    {
        return [
            $this->createMainImageField()->setLabel('Image'),
            $this->createTitleField(),
            $this->createAuthorField(),
            $this->createTagsField(),
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
            $this->createAuthorField(),
            $this->createTagsField(),
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
            $this->createTitleField()->setColumns(12),
            $this->createSlugField()->setColumns(12),
            $this->createMainImageUploadField()->setColumns(12),
            $this->createAuthorField()->setColumns(12),
            $this->createTagsField()->setColumns(12),
            $this->createIsPublishedField()->setColumns(12),
            $this->createPublishedAtField()->setColumns(12),
            CollectionField::new('contentElements', 'Contenu de l\'article')
                ->useEntryCrudForm(ArticleContentCrudController::class)
                ->setFormTypeOptions(['by_reference' => false])
                ->setColumns(12)
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

    private function createTagsField(): AssociationField
    {
        return AssociationField::new('tags', 'Tags')
            ->setFormTypeOptions([
                'by_reference' => false,
                'attr'         => ['data-ea-autocomplete-allow-item-create' => 'true'],
            ])
            ->setHelp('Tags associés à cet article. Tapez pour rechercher ou créer un nouveau tag.');
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
            ->add('tags')
            ->add('isPublished')
            ->add('publishedAt')
            ->add('createdAt')
            ->add('updatedAt');
    }
}
