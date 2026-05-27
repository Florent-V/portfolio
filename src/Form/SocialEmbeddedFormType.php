<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Social;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<Social>
 */
class SocialEmbeddedFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom du réseau',
                'attr'  => [
                    'placeholder' => 'ex: LinkedIn, GitHub, Twitter',
                    'class'       => 'form-control',
                ],
                'help' => 'Nom du réseau social ou de la plateforme',
            ])
            ->add('icon', TextType::class, [
                'label' => 'Icône',
                'attr'  => [
                    'placeholder' => 'ex: bi:linkedin, bi:github, bi:twitter',
                    'class'       => 'form-control',
                ],
                'help' => 'Nom de l\'icône Symfony UX (consultez https://ux.symfony.com/icons)',
            ])
            ->add('url', UrlType::class, [
                'label' => 'URL',
                'attr'  => [
                    'placeholder' => 'https://www.exemple.com/votre-profil',
                    'class'       => 'form-control',
                ],
                'help' => 'URL complète vers votre profil',
            ])
            ->add('sortOrder', IntegerType::class, [
                'label'    => 'Ordre d\'affichage',
                'required' => false,
                'attr'     => [
                    'placeholder' => '0',
                    'class'       => 'form-control',
                    'min'         => 0,
                    'max'         => 999,
                ],
                'help' => 'Ordre d\'affichage (0 = premier, laissez vide pour la fin)',
            ])
            ->add('isActive', CheckboxType::class, [
                'label'    => 'Actif',
                'required' => false,
                'attr'     => [
                    'class' => 'form-check-input',
                ],
                'help' => 'Cochez pour afficher ce lien sur le site',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Social::class,
        ]);
    }
}
