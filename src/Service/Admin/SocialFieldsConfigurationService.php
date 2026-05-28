<?php

declare(strict_types=1);

namespace App\Service\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class SocialFieldsConfigurationService extends AbstractFieldsConfigurationService
{
    /**
     * @return FieldInterface[]
     */
    protected function buildIndexFields(): array
    {
        return [
            $this->createNameWithIconField(),
            $this->createUrlField('url', 'URL')->setHelp('URL du lien social'),
            $this->createIntegerField('sortOrder', 'Ordre')->setHelp('Ordre d\'affichage (0 = premier)'),
            $this->createBooleanField('isActive', 'Actif')->setHelp('Lien visible sur le site'),
            $this->createAssociationField('aboutMe', 'Profil')->setHelp('Profil associé'),
            $this->createIsDeletedField(),
        ];
    }

    /**
     * @return FieldInterface[]
     */
    protected function buildDetailFields(): array
    {
        return [
            $this->createTextField('name', 'Nom')
                ->setHelp('Nom du réseau social'),
            $this->createIconPreviewField(),
            $this->createUrlField('url', 'URL')
                ->setHelp('URL complète du lien'),
            $this->createIntegerField('sortOrder', 'Ordre d\'affichage')
                ->setHelp('Ordre d\'affichage (0 = premier)'),
            $this->createBooleanField('isActive', 'Actif')
                ->setHelp('Lien visible sur le site'),
            $this->createAssociationField('aboutMe', 'Profil associé')
                ->setHelp('Profil auquel ce lien est associé'),
            $this->createdAtField(),
            $this->updatedAtField(),
            $this->createIsDeletedField(),
        ];
    }

    /**
     * @param AdminContext<object>|null $context
     *
     * @return FieldInterface[]
     */
    protected function buildFormFields(?AdminContext $context = null): array
    {
        return [
            $this->createTextField('name', 'Nom')
                ->setRequired(true)
                ->setColumns(6)
                ->setHelp('Nom du réseau social (ex: LinkedIn, GitHub, Twitter)'),
            $this->createTextField('icon', 'Icône')
                ->setRequired(true)
                ->setColumns(6)
                ->setHelp('Nom de l\'icône Symfony UX (ex: "bi:linkedin", "bi:github")'),
            $this->createUrlField('url', 'URL')
                ->setRequired(true)
                ->setColumns(12)
                ->setHelp('URL complète du lien (ex: https://www.linkedin.com/in/votreprofil)'),
            $this->createIntegerField('sortOrder', 'Ordre d\'affichage')
                ->setColumns(6)
                ->setHelp('Ordre d\'affichage (0 = premier, laissez vide pour la fin)'),
            $this->createBooleanField('isActive', 'Actif')
                ->setColumns(6)
                ->setHelp('Cochez pour afficher ce lien sur le site'),
            $this->createAssociationField('aboutMe', 'Profil')
                ->setRequired(true)
                ->setColumns(12)
                ->setHelp('Profil auquel associer ce lien social'),
        ];
    }

    private function createNameWithIconField(): TextField
    {
        return $this->createTextField('name', 'Réseau social');
    }

    private function createIconPreviewField(): TextField
    {
        return $this->createTextField('icon', 'Aperçu icône')
            ->setTemplatePath('admin/field/generic_icon_preview.html.twig');
    }
}
