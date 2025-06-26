<?php

declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ContactFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label'       => 'Votre Nom',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer votre nom.'
                    ]),
                    new Length([
                        'min' => 2,
                        'minMessage' => 'Votre nom doit comporter au moins {{ limit }} caractères.'
                    ]),
                ],
                'attr' => [
                    'placeholder' => 'John Doe',
                    'class'       => 'input input-bordered w-full',
                ],
            ])
            ->add('email', EmailType::class, [
                'label'       => 'Votre Email',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer votre adresse email.'
                    ]),
                    new Email([
                        'message' => 'L\'adresse email "{{ value }}" n\'est pas valide.'
                    ]),
                ],
                'attr' => [
                    'placeholder' => 'john.doe@example.com',
                    'class'       => 'input input-bordered w-full',
                ],
            ])
            ->add('subject', TextType::class, [
                'label'       => 'Sujet',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer un sujet.'
                    ]),
                    new Length([
                        'min' => 5,
                        'minMessage' => 'Le sujet doit comporter au moins {{ limit }} caractères.'
                    ]),
                ],
                'attr' => [
                    'placeholder' => 'Demande d\'information',
                    'class'       => 'input input-bordered w-full',
                ],
            ])
            ->add('message', TextareaType::class, [
                'label'       => 'Votre Message',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer votre message.'
                    ]),
                    new Length([
                        'min' => 10,
                        'minMessage' => 'Votre message doit comporter au moins {{ limit }} caractères.'
                    ]),
                ],
                'attr' => [
                    'placeholder' => 'Votre message ici...',
                    'class'       => 'textarea textarea-bordered h-32 w-full',
                    'rows'        => 6,
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
            // 'csrf_protection' => true, // Enabled by default
            // 'csrf_field_name' => '_token',
            // 'csrf_token_id'   => 'contact_item', // Unique token ID
        ]);
    }
}
