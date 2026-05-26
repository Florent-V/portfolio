<?php

namespace App\Form;

use App\Entity\ArticleContent;
use App\Enum\ArticleContentType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Entity\ArticleImage;
use App\Entity\ArticleCodeSnippet;

class ArticleContentFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('displayOrder', NumberType::class, [
                'label' => 'Ordre',
            ])
            ->add('type', EnumType::class, [
                'class' => ArticleContentType::class,
                'label' => 'Type',
                'choice_label' => fn (ArticleContentType $choice) => match ($choice) {
                    ArticleContentType::PARAGRAPH => 'Paragraphe',
                    ArticleContentType::IMAGE => 'Image',
                    ArticleContentType::CODE => 'Code',
                },
            ])
            ->add('content', TextareaType::class, [
                'label' => 'Contenu (pour paragraphe)',
                'required' => false,
            ])
            ->add('image', EntityType::class, [
                'class' => ArticleImage::class,
                'label' => 'Image',
                'required' => false,
            ])
            ->add('codeSnippet', EntityType::class, [
                'class' => ArticleCodeSnippet::class,
                'label' => 'Extrait de code',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ArticleContent::class,
        ]);
    }
}
