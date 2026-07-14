<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\AI\Enum\AiProvider;
use App\Enum\ArticleContentFormat;
use Symfony\Component\Form\AbstractType;
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
final class ArticleHumanizerFormType extends AbstractType
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
            ->add('language', ChoiceType::class, [
                'label'   => 'Langue cible de l\'article',
                'choices' => [
                    'Français' => 'fr',
                    'Anglais'  => 'en',
                ],
                'attr' => ['class' => 'form-select'],
                'data' => 'fr',
            ])
            ->add('format', EnumType::class, [
                'class'        => ArticleContentFormat::class,
                'label'        => 'Format du contenu',
                'choice_label' => fn (ArticleContentFormat $format) => match ($format) {
                    ArticleContentFormat::HTML     => 'HTML',
                    ArticleContentFormat::MARKDOWN => 'Markdown',
                },
                'attr' => ['class' => 'form-select'],
                'data' => ArticleContentFormat::HTML,
            ])
            ->add('rawText', TextareaType::class, [
                'label'    => 'Texte brut à reformatter',
                'required' => true,
                'attr'     => [
                    'rows'        => 20,
                    'placeholder' => 'Collez ici votre texte brut, notes, brouillon... ' .
                        'L\'IA le reformattera et l\'humanisera comme si vous l\'aviez écrit.',
                    'class' => 'form-control font-monospace',
                    'style' => 'resize: vertical; min-height: 400px;',
                ],
                'constraints' => [
                    new NotBlank(message: 'Le texte ne peut pas être vide.'),
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Reformatter et humaniser',
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
