<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\ProjectImage;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;

class ProjectImageFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('imageFile', VichImageType::class, [
                'label'        => 'Image',
                'required'     => false, // Important pour l'édition, pour ne pas avoir à re-uploader
                'allow_delete' => true,
                'download_uri' => false, // On ne veut pas le lien de téléchargement ici
                'image_uri'    => false, // Ni l'aperçu direct, géré par Vich ou manuellement si besoin
                'asset_helper' => true,
            ])
            ->add('altText', TextType::class, [
                'label'    => 'Texte alternatif (pour l\'accessibilité)',
                'required' => false,
            ])
            ->add('displayOrder', IntegerType::class, [
                'label'    => 'Ordre d\'affichage',
                'required' => false,
                'attr'     => ['placeholder' => '0'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProjectImage::class,
        ]);
    }
}
