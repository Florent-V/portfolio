<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\AI\Enum\AiProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @template-extends AbstractType<void>
 */
final class ArticleSummarizerFormType extends AbstractType
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
                'choice_label' => fn (AiProvider $p) => $p->label(),
                'attr'         => ['class' => 'form-select'],
                'data'         => AiProvider::OPENROUTER,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Générer le résumé',
                'attr'  => ['class' => 'btn btn-secondary btn-lg mt-3'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['attr' => ['novalidate' => 'novalidate']]);
    }
}
