<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Project;
use App\Repository\TagRepository;
use App\Service\Admin\ProjectFieldsConfigurationService;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * @extends AbstractCrudController<Project>
 */
class ProjectCrudController extends AbstractCrudController
{
    use AdminCrudControllerTrait;

    public function __construct(
        private readonly AdminUrlGenerator $adminUrlGenerator,
        private readonly ProjectFieldsConfigurationService $fieldsService,
        private readonly TagRepository $tagRepository,
        private readonly EntityManagerInterface $em,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return Project::class;
    }

    #[\Override]
    public function configureCrud(Crud $crud): Crud
    {
        return $this->configureCommonCrud($crud, 'Projet', 'Projets')
            ->setSearchFields(['title', 'description', 'technologies.name', 'tags.name'])
            ->setDefaultSort(['startDate' => 'DESC'])
            ->addFormTheme('@VichUploader/Form/fields.html.twig');
    }

    #[\Override]
    public function configureActions(Actions $actions): Actions
    {
        return $this->configureSoftDeleteActions(
            $this->configureCommonActions($actions)
        )
            ->add(Crud::PAGE_INDEX, $this->buildDuplicateAction());
    }

    /**
     * @return FormBuilderInterface<mixed>
     */
    #[\Override]
    public function createEditFormBuilder(
        EntityDto $entityDto,
        KeyValueStore $formOptions,
        AdminContext $context,
    ): FormBuilderInterface {
        $builder = parent::createEditFormBuilder($entityDto, $formOptions, $context);
        $this->addTagsPreSubmitListener($builder);

        return $builder;
    }

    /**
     * @return FormBuilderInterface<mixed>
     */
    #[\Override]
    public function createNewFormBuilder(
        EntityDto $entityDto,
        KeyValueStore $formOptions,
        AdminContext $context,
    ): FormBuilderInterface {
        $builder = parent::createNewFormBuilder($entityDto, $formOptions, $context);
        $this->addTagsPreSubmitListener($builder);

        return $builder;
    }

    /**
     * @param FormBuilderInterface<mixed> $builder
     */
    private function addTagsPreSubmitListener(FormBuilderInterface $builder): void
    {
        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event): void {
            $data = $event->getData();
            if (!isset($data['tags']) || !is_array($data['tags'])) {
                return;
            }

            $resolvedIds = [];
            foreach ($data['tags'] as $value) {
                if (ctype_digit((string) $value)) {
                    $resolvedIds[] = $value;
                    continue;
                }

                $tag = $this->tagRepository->findOneByNameOrCreate(trim((string) $value));
                $this->em->flush();
                $resolvedIds[] = (string) $tag->getId();
            }

            $data['tags'] = $resolvedIds;
            $event->setData($data);
        });
    }

    #[\Override]
    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('title')
            ->add(EntityFilter::new('technologies', 'Technologies'))
            ->add('tags')
            ->add('startDate')
            ->add(BooleanFilter::new('published', 'Publié'));
    }

    #[\Override]
    public function configureFields(string $pageName): iterable
    {
        return $this->fieldsService->getFieldsForPage($pageName, $this->getContext());
    }

    protected function getAdminUrlGenerator(): AdminUrlGenerator
    {
        return $this->adminUrlGenerator;
    }
}
