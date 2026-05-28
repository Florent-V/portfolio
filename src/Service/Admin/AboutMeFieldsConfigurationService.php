<?php

declare(strict_types=1);

namespace App\Service\Admin;

use App\Form\SocialEmbeddedFormType;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Vich\UploaderBundle\Form\Type\VichFileType;

class AboutMeFieldsConfigurationService extends AbstractFieldsConfigurationService
{
    /**
     * @return FieldInterface[]
     */
    protected function buildIndexFields(): array
    {
        return [
            $this->createTextField('firstName', 'Prénom'),
            $this->createTextField('lastName', 'Nom'),
            $this->createTextField('title', 'Titre professionnel'),
        ];
    }

    /**
     * @return FieldInterface[]
     */
    protected function buildDetailFields(): array
    {
        return [
            $this->createTextField('firstName', 'Prénom'),
            $this->createTextField('lastName', 'Nom'),
            $this->createTextField('title', 'Titre professionnel'),
            $this->createIntegerField('yearsExperience', 'Années d\'expérience'),
            $this->createTextAreaField('description', 'Description'),
            $this->createImageField('profilePictureName', 'Photo de profil', '/uploads/images/about_me'),
            $this->createTextField('cvFileName', 'CV')
                ->setCustomOption('base_path', '/uploads/files/cv/')
                ->setTemplatePath('admin/fields/file_link.html.twig'),
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
            $this->createTextField('firstName', 'Prénom')
                ->setColumns(6)->setHelp('Votre prénom'),
            $this->createTextField('lastName', 'Nom')
                ->setColumns(6)->setHelp('Votre nom de famille'),
            $this->createTextField('title', 'Titre professionnel')
                ->setColumns(12)->setHelp('Votre titre ou poste actuel'),
            $this->createIntegerField('yearsExperience', 'Années d\'expérience')
                ->setColumns(6)->setHelp('Nombre d\'années d\'expérience professionnelle'),
            $this->createTextAreaField('description', 'Description')
                ->setColumns(12)->setNumOfRows(5)->setHelp('Brève description de votre profil professionnel'),
            $this->createVichImageUploadField('profilePictureFile', 'Photo de profil')
                ->setHelp('Téléchargez votre photo de profil (JPEG, PNG, WEBP). Max 2MB.')
                ->onlyOnForms(),
            $this->createImageField('profilePictureName', 'Photo de profil', '/uploads/images/about_me')->hideOnForm(),
            TextField::new('cvFile', 'CV (PDF)')
                ->setFormType(VichFileType::class)
                ->setHelp('Téléchargez votre CV en format PDF. Max 5MB.')
                ->setRequired(false)->onlyOnForms(),
            $this->createTextField('cvFileName', 'Nom du fichier CV')
                ->hideOnForm()
                ->setCustomOption('base_path', '/uploads/files/cv/')
                ->setTemplatePath('admin/fields/file_link.html.twig'),
            CollectionField::new('socialLinks', 'Liens sociaux')
                ->setEntryType(SocialEmbeddedFormType::class)
                ->setEntryIsComplex(true)
                ->setFormTypeOptions(['by_reference' => false])
                ->allowAdd(true)->allowDelete(true)
                ->setHelp('Liens vers les réseaux sociaux et plateformes professionnelles')
                ->onlyOnForms(),
        ];
    }
}
