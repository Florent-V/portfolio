<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Project;
use App\Form\ProjectImageFormType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use Vich\UploaderBundle\Form\Type\VichImageType;

class ProjectCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Project::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Projet')
            ->setEntityLabelInPlural('Projets')
            ->setPageTitle(Crud::PAGE_INDEX, 'Gestion des projets')
            ->setPageTitle(Crud::PAGE_EDIT, 'Modifier le projet')
            ->setPageTitle(Crud::PAGE_NEW, 'Créer un projet')
            ->setPageTitle(Crud::PAGE_DETAIL, 'Détail du projet')
            ->setSearchFields(['title', 'description', 'technologies.name'])
            ->setDefaultSort(['startDate' => 'DESC'])
            ->setPaginatorPageSize(25)
            ->showEntityActionsInlined()
            ->setAutofocusSearch()
            ->setTimezone('Europe/Paris')
            ->setHelp(Crud::PAGE_INDEX, 'Gérez vos projets et leurs technologies associées.');
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

    /** @return FieldInterface[] */
    private function getIndexFields(): array
    {
        return [
            $this->createMainImageField()->setLabel('Image'),
            $this->createTitleField(),
            $this->createTechnologiesField(),
            $this->createStartDateField(),
            $this->createEndDateField(),
            $this->createPublishedField(),
        ];
    }

    /** @return FieldInterface[] */
    private function getDetailFields(): array
    {
        return [
            $this->createMainImageField()->setLabel('Image Principale'),
            $this->createTitleField(),
            $this->createDescriptionDetailField(),
            $this->createTechnologiesField(),
            $this->createUrlField(),
            $this->createRepositoryUrlField(),
            $this->createStartDateField(),
            $this->createEndDateField(),
            $this->createPublishedField(),
            $this->createProjectImagesField(),
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
            $this->createDescriptionField(),
            $this->createTechnologiesField(),
            $this->createUrlField(),
            $this->createRepositoryUrlField(),
            $this->createMainImageUploadField(),
            $this->createStartDateField(),
            $this->createEndDateField(),
            $this->createPublishedField(),
            $this->createProjectImagesField(),
        ];
    }

    private function createTitleField(): TextField
    {
        return TextField::new('title', 'Titre');
    }

    private function createDescriptionField(): TextEditorField
    {
        return TextEditorField::new('description', 'Description')
            ->setNumOfRows(15);
    }

    private function createDescriptionDetailField(): TextareaField
    {
        return TextareaField::new('description', 'Description')
            ->renderAsHtml();
    }

    private function createTechnologiesField(): AssociationField
    {
        return AssociationField::new('technologies', 'Technologies')
            ->setCrudController(TechnologyCrudController::class)
            ->setTemplatePath('admin/fields/project_technologies.html.twig')
            ->setFormTypeOptions(['by_reference' => false]);
    }

    private function createUrlField(): UrlField
    {
        return UrlField::new('url', 'URL du projet')
            ->setHelp('URL publique du projet');
    }

    private function createRepositoryUrlField(): UrlField
    {
        return UrlField::new('repositoryUrl', 'URL du dépôt')
            ->setHelp('URL du dépôt source (GitHub, GitLab, etc.)');
    }

    private function createMainImageField(): ImageField
    {
        return ImageField::new('mainImageName', 'Image')
            ->setBasePath('/uploads/images/projects')
            ->setHelp('Image principale qui sera affichée en premier.');
    }

    private function createMainImageUploadField(): TextareaField
    {
        return TextareaField::new('mainImageFile', 'Image Principale')
            ->setFormType(VichImageType::class)
            ->setHelp('Image principale du projet (JPEG, PNG, WEBP). Max 5MB.')
            ->setRequired(true);
    }

    private function createStartDateField(): DateTimeField
    {
        return DateTimeField::new('startDate', 'Date de début')
            ->setFormat('yyyy-MM-dd')
            ->renderAsNativeWidget()
            ->setHelp('Date de début du projet');
    }

    private function createEndDateField(): DateTimeField
    {
        return DateTimeField::new('endDate', 'Date de fin')
            ->setFormat('yyyy-MM-dd')
            ->renderAsNativeWidget()
            ->setRequired(false)
            ->setHelp('Date de fin du projet (optionnel). Si le projet est en cours, laissez vide.');
    }

    private function createPublishedField(): BooleanField
    {
        return BooleanField::new('published', 'Publié');
    }

    private function createProjectImagesField(): CollectionField
    {
        return CollectionField::new('projectImages', 'Galerie d\'images')
            ->setEntryType(ProjectImageFormType::class)
            ->setFormTypeOptions(['by_reference' => false])
            ->setHelp('Ajoutez des images supplémentaires pour la galerie du projet.');
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
                ->setIcon('fa fa-project-diagram')
                ->setLabel('Add Project')
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
            ->add('technologies')
            ->add('startDate')
            ->add('published');
    }
}
