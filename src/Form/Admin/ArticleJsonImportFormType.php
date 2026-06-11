<?php

declare(strict_types=1);

namespace App\Form\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * @template-extends AbstractType<void>
 */
final class ArticleJsonImportFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('jsonFile', FileType::class, [
                'label'       => 'Fichier JSON',
                'mapped'      => false,
                'required'    => true,
                'attr'        => ['accept' => '.json,application/json,text/plain'],
                'constraints' => [
                    new NotBlank(message: 'Veuillez sélectionner un fichier JSON.'),
                    new File(
                        maxSize: '2M',
                        maxSizeMessage: 'Le fichier ne doit pas dépasser 2 Mo.',
                    ),
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label'      => 'Importer l\'article',
                'label_html' => true,
                'attr'       => ['class' => 'btn btn-primary btn-lg'],
            ]);
    }
}
