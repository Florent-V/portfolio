<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\AboutMe;
use App\Entity\Article;
use App\Entity\Education;
use App\Entity\Experience;
use App\Entity\Hobby;
use App\Entity\Project;
use App\Entity\ProjectImage;
use App\Entity\SkillCategory;
use App\Entity\Social;
use App\Entity\SoftSkill;
use App\Entity\Technology;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        /** @var User $adminUser */
        $adminUser = $this->getReference(UserFixtures::ADMIN_USER_REFERENCE, User::class);

        // Create AboutMe with Social links
        $this->createAboutMe($manager, $adminUser);

        // Create Skills
        $technologies = $this->createSkills($manager, $adminUser);

        // Create other entities
        $this->createArticle($manager, $adminUser, $technologies);
        $this->createEducation($manager, $adminUser);
        $this->createExperience($manager, $adminUser);
        $this->createHobbies($manager, $adminUser);
        $this->createProject($manager, $adminUser, $technologies);
        $this->createSoftSkills($manager, $adminUser);

        $manager->flush();
    }

    private function createAboutMe(ObjectManager $manager, User $user): AboutMe
    {
        $aboutMe = new AboutMe();
        $aboutMe->setFirstName('Florent');
        $aboutMe->setLastName('Vasseur');
        $aboutMe->setTitle('Développeur Web Full-Stack passionné');
        $aboutMe->setDescription(
            "Avec plusieurs années d'expérience dans la création d'applications web robustes et élégantes, je suis spécialisé dans l'écosystème Symfony et Vue.js. J'aime transformer des idées complexes en solutions performantes et intuitives. Toujours en quête de nouvelles connaissances, je suis un fervent adepte des bonnes pratiques et de l'amélioration continue."
        );
        $aboutMe->setYearsExperience(5);
        $aboutMe->setCreatedBy($user);
        $manager->persist($aboutMe);

        $social1 = new Social();
        $social1->setName('LinkedIn');
        $social1->setUrl('https://www.linkedin.com/in/florent-vasseur-549939153/');
        $social1->setIcon('skill-icons:linkedin');
        $social1->setSortOrder(1);
        $social1->setAboutMe($aboutMe);
        $social1->setCreatedBy($user);
        $manager->persist($social1);

        $social2 = new Social();
        $social2->setName('GitHub');
        $social2->setUrl('https://github.com/FlorentVasseur');
        $social2->setIcon('skill-icons:github-dark');
        $social2->setSortOrder(2);
        $social2->setAboutMe($aboutMe);
        $social2->setCreatedBy($user);
        $manager->persist($social2);

        return $aboutMe;
    }

    /**
     * @return array<string, Technology>
     */
    private function createSkills(ObjectManager $manager, User $user): array
    {
        $backendCategory = new SkillCategory();
        $backendCategory->setName('Backend');
        $backendCategory->setIcon('hugeicons:developer');
        $backendCategory->setDisplayOrder(1);
        $backendCategory->setCreatedBy($user);
        $manager->persist($backendCategory);

        $frontendCategory = new SkillCategory();
        $frontendCategory->setName('Frontend');
        $frontendCategory->setIcon('ph:devices');
        $frontendCategory->setDisplayOrder(2);
        $frontendCategory->setCreatedBy($user);
        $manager->persist($frontendCategory);

        $toolsCategory = new SkillCategory();
        $toolsCategory->setName('Outils & DevOps');
        $toolsCategory->setIcon('la:tools');
        $toolsCategory->setDisplayOrder(3);
        $toolsCategory->setCreatedBy($user);
        $manager->persist($toolsCategory);

        $technologies = [];

        $techData = [
            ['PHP', $backendCategory, 5, 'skill-icons:php-dark'],
            ['Symfony', $backendCategory, 5, 'skill-icons:symfony-dark'],
            ['MySQL', $backendCategory, 4, 'skill-icons:mysql-dark'],
            ['JavaScript', $frontendCategory, 4, 'skill-icons:javascript'],
            ['Vue.js', $frontendCategory, 4, 'skill-icons:vuejs-dark'],
            ['Tailwind CSS', $frontendCategory, 5, 'skill-icons:tailwindcss-dark'],
            ['Docker', $toolsCategory, 4, 'skill-icons:docker'],
            ['Git', $toolsCategory, 5, 'skill-icons:git'],
            ['Webpack', $toolsCategory, 3, 'skill-icons:webpack-dark'],
        ];

        foreach ($techData as [$name, $category, $level, $icon]) {
            $tech = new Technology();
            $tech->setName($name);
            $tech->setCategory($category);
            $tech->setLevel($level);
            $tech->setIcon($icon);
            $tech->setCreatedBy($user);
            $manager->persist($tech);
            $technologies[strtolower($name)] = $tech;
        }

        return $technologies;
    }

    private function createArticle(ObjectManager $manager, User $user, array $technologies): void
    {
        $article = new Article();
        $article->setTitle('Optimiser les performances d\'une application Symfony');
        $article->setSlug('optimiser-performances-symfony');
        $article->setContent(
            "L'optimisation des performances est cruciale. Dans cet article, nous explorons diverses techniques pour accélérer une application Symfony, de la configuration du cache avec Redis à l'optimisation des requêtes Doctrine, en passant par l'utilisation de Webpack Encore pour les assets frontend."
        );
        $article->setAuthor($user);
        $article->setIsPublished(true);
        $article->setPublishedAt(new \DateTimeImmutable('-10 days'));
        $article->addTechnology($technologies['symfony']);
        $article->addTechnology($technologies['php']);
        $article->setCreatedBy($user);
        $manager->persist($article);
    }

    private function createEducation(ObjectManager $manager, User $user): void
    {
        $education = new Education();
        $education->setDegree('Master en Ingénierie Logicielle');
        $education->setInstitution('Université de Technologie');
        $education->setStartDate(new \DateTime('2015-09-01'));
        $education->setEndDate(new \DateTime('2017-07-01'));
        $education->setDescription('Spécialisation en développement d\'applications web et architectures distribuées.');
        $education->setCreatedBy($user);
        $manager->persist($education);
    }

    private function createExperience(ObjectManager $manager, User $user): void
    {
        $experience = new Experience();
        $experience->setJobTitle('Développeur Web Senior');
        $experience->setCompany('Innovatech Solutions');
        $experience->setStartDate(new \DateTime('2017-09-01'));
        $experience->setIsCurrent(true);
        $experience->setDescription(
            "- Conception et développement d'applications web sur mesure avec Symfony.\n- Migration d'architectures monolithiques vers des microservices.\n- Encadrement technique de l'équipe de développeurs juniors."
        );
        $experience->setCreatedBy($user);
        $manager->persist($experience);
    }

    private function createHobbies(ObjectManager $manager, User $user): void
    {
        $hobby1 = new Hobby();
        $hobby1->setName('Randonnée');
        $hobby1->setIcon('material-symbols-light:hiking');
        $hobby1->setCreatedBy($user);
        $manager->persist($hobby1);

        $hobby2 = new Hobby();
        $hobby2->setName('Photographie');
        $hobby2->setIcon('mdi-light:camera');
        $hobby2->setCreatedBy($user);
        $manager->persist($hobby2);

        $hobby3 = new Hobby();
        $hobby3->setName('Jeux de stratégie');
        $hobby3->setIcon('mingcute:chess-line');
        $hobby3->setCreatedBy($user);
        $manager->persist($hobby3);
    }

    private function createProject(ObjectManager $manager, User $user, array $technologies): void
    {
        $project = new Project();
        $project->setTitle('Portfolio Personnel V2');
        $project->setDescription(
            "Refonte complète de mon portfolio pour mettre en œuvre les dernières fonctionnalités de Symfony et Vue.js. L'accent a été mis sur un design épuré, des animations fluides et une interface d'administration complète et intuitive avec EasyAdmin."
        );
        $project->setStartDate(new \DateTime('2024-01-01'));
        $project->setPublished(true);
        $project->setUrl('https://mon-portfolio.dev');
        $project->setRepositoryUrl('https://github.com/FlorentVasseur/portfolio');
        $project->addTechnology($technologies['symfony']);
        $project->addTechnology($technologies['vue.js']);
        $project->addTechnology($technologies['tailwind css']);
        $project->addTechnology($technologies['docker']);
        $project->setCreatedBy($user);
        $manager->persist($project);

        $image1 = new ProjectImage();
        $image1->setProject($project);
        $image1->setAltText('Page d\'accueil du portfolio');
        $image1->setDisplayOrder(1);
        $image1->setCreatedBy($user);
        $manager->persist($image1);
    }

    private function createSoftSkills(ObjectManager $manager, User $user): void
    {
        $softSkill1 = new SoftSkill();
        $softSkill1->setName('Communication');
        $softSkill1->setDescription('Capacité à exprimer des idées claires et à collaborer efficacement avec les équipes techniques et non-techniques.');
        $softSkill1->setIcon('material-symbols:communication');
        $softSkill1->setDisplayOrder(1);
        $softSkill1->setCreatedBy($user);
        $manager->persist($softSkill1);

        $softSkill2 = new SoftSkill();
        $softSkill2->setName('Résolution de problèmes');
        $softSkill2->setDescription('Analyse rigoureuse des problèmes pour trouver des solutions robustes et pérennes.');
        $softSkill2->setIcon('mdi:lightbulb-outline');
        $softSkill2->setDisplayOrder(2);
        $softSkill2->setCreatedBy($user);
        $manager->persist($softSkill2);

        $softSkill3 = new SoftSkill();
        $softSkill3->setName('Apprentissage continu');
        $softSkill3->setDescription('Veille technologique active et curiosité pour les nouvelles technologies et méthodologies.');
        $softSkill3->setIcon('fa:book-reader');
        $softSkill3->setDisplayOrder(3);
        $softSkill3->setCreatedBy($user);
        $manager->persist($softSkill3);
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
        ];
    }
}
