<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\AI\Enum\AiProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * @template-extends AbstractType<void>
 */
final class ArticleGeneratorFormType extends AbstractType
{
    /**
     * @param FormBuilderInterface<mixed> $builder
     * @param array<string, mixed>        $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('provider', EnumType::class, [
                'class'        => AiProvider::class,
                'label'        => 'Fournisseur IA',
                'choice_label' => fn (AiProvider $provider) => $provider->label(),
                'attr'         => ['class' => 'form-select'],
                'data'         => AiProvider::OPENROUTER,
            ])
            ->add('topic', TextareaType::class, [
                'label'    => 'Thème / Sujet',
                'required' => true,
                'attr'     => [
                    'rows'        => 4,
                    'placeholder' => 'Ex: Les nouveautés de PHP 8.4 et leurs cas d\'usage pratiques',
                    'class'       => 'form-control',
                ],
                'constraints' => [
                    new NotBlank(message: 'Le thème ne peut pas être vide.'),
                ],
            ])
            ->add('language', ChoiceType::class, [
                'label'   => 'Langue de l\'article',
                'choices' => [
                    'Français' => 'fr',
                    'Anglais'  => 'en',
                ],
                'attr' => ['class' => 'form-select'],
                'data' => 'fr',
            ])
            ->add('useWebSearch', CheckboxType::class, [
                'label'    => 'Activer la recherche web (OpenRouter uniquement)',
                'required' => false,
                'attr'     => ['class' => 'form-check-input'],
            ])
            ->add('extraInstructions', TextareaType::class, [
                'label'    => 'Instructions supplémentaires (optionnel)',
                'required' => false,
                'attr'     => [
                    'rows'        => 3,
                    'placeholder' => 'Ex: Inclure des exemples de code Symfony,' .
                        ' cibler les développeurs débutants, etc.',
                    'class' => 'form-control',
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Générer l\'article',
                'attr'  => ['class' => 'btn btn-primary btn-lg mt-3'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'attr' => ['novalidate' => 'novalidate'],
        ]);
    }
}
