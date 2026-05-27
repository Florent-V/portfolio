<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\AboutMe;
use App\Form\SocialEmbeddedFormType;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Vich\UploaderBundle\Form\Type\VichFileType;
use Vich\UploaderBundle\Form\Type\VichImageType;

class AboutMeCrudController extends AbstractCrudController
{
    private EntityManagerInterface $entityManager;
    private AdminUrlGenerator $adminUrlGenerator;

    public function __construct(EntityManagerInterface $entityManager, AdminUrlGenerator $adminUrlGenerator)
    {
        $this->entityManager     = $entityManager;
        $this->adminUrlGenerator = $adminUrlGenerator;
    }

    public static function getEntityFqcn(): string
    {
        return AboutMe::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('À Propos de Moi')
            ->setEntityLabelInPlural('À Propos de Moi')
            ->setPageTitle('index', 'Gestion de la section "À Propos de Moi"')
            ->setPageTitle('edit', 'Modifier la section "À Propos de Moi"');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('firstName', 'Prénom')
                ->setColumns(6)
                ->setHelp('Votre prénom'),
            TextField::new('lastName', 'Nom')
                ->setColumns(6)
                ->setHelp('Votre nom de famille'),
            TextField::new('title', 'Titre professionnel')
                ->setColumns(12)
                ->setHelp('Votre titre ou poste actuel'),
            IntegerField::new('yearsExperience', 'Années d\'expérience')
                ->setColumns(6)
                ->setHelp('Nombre d\'années d\'expérience professionnelle'),
            TextareaField::new('description', 'Description')
                ->setColumns(12)
                ->setNumOfRows(5)
                ->setHelp('Une brève description de votre profil professionnel'),

            TextareaField::new('profilePictureFile', 'Photo de profil')
                ->setFormType(VichImageType::class)
                ->setHelp('Téléchargez votre photo de profil (JPEG, PNG, WEBP). Max 2MB.')
                ->setRequired(false) // Allow empty to keep existing image
                ->onlyOnForms(),
            ImageField::new('profilePictureName', 'Photo de profil')
                ->setBasePath('/uploads/images/about_me')
                ->hideOnForm(),

            TextareaField::new('cvFile', 'CV (PDF)')
                ->setFormType(VichFileType::class)
                ->setHelp('Téléchargez votre CV en format PDF. Max 5MB.')
                ->setRequired(false) // Allow empty to keep existing file
                ->onlyOnForms(),
            TextField::new('cvFileName', 'Nom du fichier CV')
                ->hideOnForm()
                ->setCustomOption('base_path', '/uploads/files/cv/')
                ->setTemplatePath('admin/fields/file_link.html.twig'),

            CollectionField::new('socialLinks', 'Liens sociaux')
                ->setEntryType(SocialEmbeddedFormType::class)
                ->setEntryIsComplex(true)
                ->setFormTypeOptions(['by_reference' => false])
                ->allowAdd(true)
                ->allowDelete(true)
                ->setHelp('Ajoutez vos liens vers les réseaux sociaux et plateformes professionnelles')
                ->onlyOnForms(),
        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        $count = $this->entityManager->getRepository(AboutMe::class)->count([]);

        // Always disable batch delete for this singleton
        $actions->disable(Action::BATCH_DELETE);

        if ($count > 0) {
            // If an entry exists, disable NEW and DELETE
            $actions->disable(Action::NEW, Action::DELETE);

            return $actions;
        }
        // If no entry exists, still disable DELETE
        $actions->disable(Action::DELETE);

        return $actions;
    }

    /**
     * Redirect to the edit page of the single AboutMe entry if it exists from index.
     * If it doesn't exist, stay on index (which will show the "New" button).
     */
    public function index(AdminContext $context)
    {
        $aboutMeEntry = $this->entityManager->getRepository(AboutMe::class)->findOneBy([]);

        if ($aboutMeEntry) {
            $url = $this->adminUrlGenerator
                ->setController(self::class)
                ->setAction(Action::EDIT)
                ->setEntityId($aboutMeEntry->getId())
                ->generateUrl();

            return $this->redirect($url);
        }

        return parent::index($context);
    }
}
