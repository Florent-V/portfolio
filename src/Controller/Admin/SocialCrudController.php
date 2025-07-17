<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Social;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\NumericFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;

class SocialCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Social::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            // Labels et titre
            ->setEntityLabelInSingular('Lien Social')
            ->setEntityLabelInPlural('Liens Sociaux')
            ->setPageTitle('index', 'Gestion des %entity_label_plural%')
            ->setPageTitle('new', 'Ajouter un %entity_label_singular%')
            ->setPageTitle('edit', 'Modifier le %entity_label_singular%')
            ->setPageTitle('detail', 'Détails du %entity_label_singular%')

            // Recherche
            ->setSearchFields(['name', 'url', 'icon', 'aboutMe.firstName', 'aboutMe.lastName'])
            ->setAutofocusSearch()

            // Tri par défaut
            ->setDefaultSort(['sortOrder' => 'ASC', 'name' => 'ASC'])

            // Pagination
            ->setPaginatorPageSize(25)
            ->setPaginatorRangeSize(4)

            // Actions inline
            ->showEntityActionsInlined()

            // Timezone
            ->setTimezone('Europe/Paris')

            // Help
            ->setHelp('index', 'Gérez les liens vers les réseaux sociaux et plateformes en ligne. 
                Les liens sont affichés dans la section "À propos" du portfolio selon leur ordre de tri.')
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            // Action NEW
            ->update(Crud::PAGE_INDEX, Action::NEW, static function (Action $action) {
                return $action
                    ->setLabel('Nouveau Lien')
                    ->setIcon('fas fa-plus')
                    ->setCssClass('btn btn-primary');
            })

            // Action EDIT
            ->update(Crud::PAGE_INDEX, Action::EDIT, static function (Action $action) {
                return $action
                    ->setLabel('Modifier')
                    ->setIcon('fas fa-edit')
                    ->setCssClass('btn btn-sm btn-outline-secondary');
            })

            // Action DELETE
            ->update(Crud::PAGE_INDEX, Action::DELETE, static function (Action $action) {
                return $action
                    ->setLabel('Supprimer')
                    ->setIcon('fas fa-trash')
                    ->setCssClass('btn btn-sm btn-outline-danger');
            })

            // Action DETAIL
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(Crud::PAGE_INDEX, Action::DETAIL, static function (Action $action) {
                return $action
                    ->setLabel('Détails')
                    ->setIcon('fas fa-eye')
                    ->setCssClass('btn btn-sm btn-outline-info');
            })

            // Actions pour les autres pages
            ->update(Crud::PAGE_DETAIL, Action::EDIT, static function (Action $action) {
                return $action->setIcon('fas fa-edit');
            })
            ->update(Crud::PAGE_DETAIL, Action::DELETE, static function (Action $action) {
                return $action->setIcon('fas fa-trash');
            })
            ->update(Crud::PAGE_DETAIL, Action::INDEX, static function (Action $action) {
                return $action->setIcon('fas fa-arrow-left');
            })
        ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(TextFilter::new('name', 'Nom'))
            ->add(TextFilter::new('url', 'URL'))
            ->add(TextFilter::new('icon', 'Icône'))
            ->add(BooleanFilter::new('isActive', 'Actif'))
            ->add(NumericFilter::new('sortOrder', 'Ordre'))
            ->add(EntityFilter::new('aboutMe', 'Profil'))
            ->add(DateTimeFilter::new('createdAt', 'Date de création'))
            ->add(DateTimeFilter::new('updatedAt', 'Date de modification'))
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        return match ($pageName) {
            Crud::PAGE_INDEX  => $this->getIndexFields(),
            Crud::PAGE_DETAIL => $this->getDetailFields(),
            default           => $this->getFormFields(),
        };
    }

    /**
     * @return FieldInterface[]
     */
    private function getIndexFields(): array
    {
        return [
            $this->createNameWithIconField(),
            UrlField::new('url', 'URL')
                ->setHelp('URL du lien social'),
            IntegerField::new('sortOrder', 'Ordre')
                ->setHelp('Ordre d\'affichage (0 = premier)'),
            BooleanField::new('isActive', 'Actif')
                ->setHelp('Lien visible sur le site'),
            AssociationField::new('aboutMe', 'Profil')
                ->setHelp('Profil associé'),
        ];
    }

    /**
     * @return FieldInterface[]
     */
    private function getDetailFields(): array
    {
        return [
            TextField::new('name', 'Nom')
                ->setHelp('Nom du réseau social ou de la plateforme'),
            $this->createIconPreviewField(),
            UrlField::new('url', 'URL')
                ->setHelp('URL complète du lien'),
            IntegerField::new('sortOrder', 'Ordre d\'affichage')
                ->setHelp('Ordre d\'affichage (0 = premier)'),
            BooleanField::new('isActive', 'Actif')
                ->setHelp('Lien visible sur le site'),
            AssociationField::new('aboutMe', 'Profil associé')
                ->setHelp('Profil auquel ce lien est associé'),
            DateTimeField::new('createdAt', 'Créé le')
                ->setFormat('dd/MM/yyyy HH:mm')
                ->setTimezone('Europe/Paris')
                ->setHelp('Date de création'),
            DateTimeField::new('updatedAt', 'Modifié le')
                ->setFormat('dd/MM/yyyy HH:mm')
                ->setTimezone('Europe/Paris')
                ->setHelp('Date de dernière modification'),
        ];
    }

    /**
     * @return FieldInterface[]
     */
    private function getFormFields(): array
    {
        return [
            TextField::new('name', 'Nom')
                ->setRequired(true)
                ->setColumns(6)
                ->setHelp('Nom du réseau social (ex: LinkedIn, GitHub, Twitter)'),
            TextField::new('icon', 'Icône')
                ->setRequired(true)
                ->setColumns(6)
                ->setHelp('Nom de l\'icône Symfony UX (ex: "bi:linkedin", "bi:github", "bi:twitter")'),
            UrlField::new('url', 'URL')
                ->setRequired(true)
                ->setColumns(12)
                ->setHelp('URL complète du lien (ex: https://www.linkedin.com/in/votreprofil)'),
            IntegerField::new('sortOrder', 'Ordre d\'affichage')
                ->setColumns(6)
                ->setHelp('Ordre d\'affichage (0 = premier, laissez vide pour la fin)'),
            BooleanField::new('isActive', 'Actif')
                ->setColumns(6)
                ->setHelp('Cochez pour afficher ce lien sur le site'),
            AssociationField::new('aboutMe', 'Profil')
                ->setRequired(true)
                ->setColumns(12)
                ->setHelp('Profil auquel associer ce lien social'),
        ];
    }

    private function createNameWithIconField(): FieldInterface
    {
        return TextField::new('name', 'Nom')
            ->setTemplatePath('admin/field/generic_name_with_icon.html.twig')
            ->setHelp('Nom du réseau social avec aperçu de l\'icône');
    }

    private function createIconPreviewField(): FieldInterface
    {
        return TextField::new('icon', 'Icône')
            ->setTemplatePath('admin/field/generic_icon_preview.html.twig')
            ->setHelp('Icône Symfony UX avec aperçu');
    }
}
