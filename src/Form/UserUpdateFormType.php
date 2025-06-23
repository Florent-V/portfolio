<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichFileType;

/**
 * @extends AbstractType<User>
 */
class UserUpdateFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('userName', TextType::class, [
                'label' => 'Nom d\'utilisateur',
            ])
            ->add('email', EmailType::class, [
                'label' => 'Adresse e-mail',
            ])
            ->add('firstName', TextType::class, [
                'label'    => 'Prénom',
                'required' => false,
            ])
            ->add('lastName', TextType::class, [
                'label'    => 'Nom de famille',
                'required' => false,
            ])
            ->add('phone', TextType::class, [
                'label'    => 'Téléphone',
                'required' => false,
            ])
            ->add('pictureFile', VichFileType::class, [
                'label'        => 'Photo de profil',
                'attr'         => ['placeholder' => 'photo'],
                'required'     => false,
                'allow_delete' => true,
                'download_uri' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
