<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\ProjectImage;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use Vich\UploaderBundle\Form\Type\VichImageType;

class ProjectImageCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ProjectImage::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Image de Projet')
            ->setEntityLabelInPlural('Images de Projet')
            ->setPageTitle(Crud::PAGE_INDEX, 'Gestion des images de projet')
            ->setPageTitle(Crud::PAGE_EDIT, 'Modifier l\'image')
            ->setPageTitle(Crud::PAGE_NEW, 'Ajouter une image')
            ->setPageTitle(Crud::PAGE_DETAIL, 'Détail de l\'image')
            ->setDefaultSort(['project' => 'ASC', 'displayOrder' => 'ASC'])
            ->setPaginatorPageSize(20)
            ->setHelp(Crud::PAGE_INDEX, 'Gérez les images associées à vos projets.');
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->update(Crud::PAGE_INDEX, Action::NEW, fn (Action $action) => $action
                ->setIcon('fa fa-image')
                ->setLabel('Add Image')
                ->setCssClass('btn btn-success'))
            ->update(Crud::PAGE_INDEX, Action::EDIT, fn (Action $action) => $action->setIcon('fa fa-edit'))
            ->update(Crud::PAGE_INDEX, Action::DELETE, fn (Action $action) => $action->setIcon('fa fa-trash'))
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(Crud::PAGE_INDEX, Action::DETAIL, fn (Action $action) => $action->setIcon('fa fa-eye'));
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('project', 'Projet'));
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
            $this->createImageField()->setLabel('Image'),
            $this->createProjectField(),
            $this->createDisplayOrderField(),
            $this->createAltTextField(),
        ];
    }

    /** @return FieldInterface[] */
    private function getDetailFields(): array
    {
        return [
            $this->createImageField()->setLabel('Image'),
            $this->createProjectField(),
            $this->createDisplayOrderField(),
            $this->createAltTextField(),
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
            $this->createProjectField(),
            $this->createImageUploadField(),
            $this->createDisplayOrderField(),
            $this->createAltTextField(),
        ];
    }

    private function createProjectField(): AssociationField
    {
        return AssociationField::new('project', 'Projet');
    }

    private function createImageField(): ImageField
    {
        return ImageField::new('imageName', 'Image')
            ->setBasePath('/uploads/images/projects/gallery');
    }

    private function createImageUploadField(): TextareaField
    {
        return TextareaField::new('imageFile', 'Image')
            ->setFormType(VichImageType::class)
            ->setHelp('Image pour la galerie du projet. Taille max 5MB.')
            ->setRequired(true);
    }

    private function createDisplayOrderField(): NumberField
    {
        return NumberField::new('displayOrder', 'Ordre d\'affichage');
    }

    private function createAltTextField(): TextField
    {
        return TextField::new('altText', 'Texte alternatif');
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
}
