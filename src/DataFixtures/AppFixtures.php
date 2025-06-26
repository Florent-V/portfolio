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
use App\Entity\SoftSkill;
use App\Entity\Technology;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $adminUser = $this->loadAdminUser($manager);
        $this->loadAboutMe($manager);
        $technologies = $this->loadSkillsAndTechnologies($manager);
        $this->loadProjects($manager, $technologies);
        $this->loadEducation($manager);
        $this->loadExperience($manager);
        $this->loadHobbies($manager);
        $this->loadArticles($manager, $adminUser, $technologies);
        $this->loadSoftSkills($manager);

        $manager->flush();
    }

    private function loadAdminUser(ObjectManager $manager): User
    {
        $adminUser = $manager
            ->getRepository(User::class)
            ->findOneBy(
                [
                    'email' => 'superadmin@omega.com',
                ]
            );
        if (!$adminUser) {
            $adminUser = new User();
            $adminUser->setEmail('superadmin@omega.com');
            $adminUser->setUserName('Flo');
            $adminUser->setFirstName('Florent');
            $adminUser->setLastName('Vasseur');
            $adminUser->setRoles(['ROLE_SUPER_ADMIN', 'ROLE_ADMIN']);
            $adminUser->setPassword($this->passwordHasher->hashPassword($adminUser, 'password'));
            $adminUser->setIsVerified(true);
            $manager->persist($adminUser);
        }

        return $adminUser;
    }

    private function loadAboutMe(ObjectManager $manager): void
    {
        $aboutMe = new AboutMe();
        $aboutMe->setTitle('John Doe - Développeur Web Full-Stack');
        $aboutMe->setDescription(
            "Passionné par la création d'applications web intuitives et performantes, " .
            "j'ai plusieurs années d'expérience avec des technologies modernes. " .
            "J'aime transformer des idées complexes en solutions élégantes et fonctionnelles. \n\n" .
            "Autodidacte, curieux et toujours à l'affût de nouvelles technologies, " .
            "je m'épanouis dans les environnements stimulants " .
            "où je peux apprendre et partager mes connaissances. \n\n" .
            "En dehors du code, j'apprécie la randonnée, la photographie et les jeux de stratégie."
        );
        $aboutMe->setCvFileName('test.pdf');
        $aboutMe->setProfilePictureName('test-600x400.png');
        $manager->persist($aboutMe);
    }

    private function loadSkillsAndTechnologies(ObjectManager $manager): array
    {
        $scBackend = new SkillCategory();
        $scBackend->setName('Backend');
        $scBackend->setDisplayOrder(1);
        $manager->persist($scBackend);
        $php = new Technology();
        $php->setName('PHP');
        $php->setCategory($scBackend);
        $php->setLevel(5);
        $php->setImageName('test-600x400.png');
        $manager->persist($php);
        $symfony = new Technology();
        $symfony->setName('Symfony');
        $symfony->setCategory($scBackend);
        $symfony->setLevel(5);
        $symfony->setImageName('test-600x400.png');
        $manager->persist($symfony);
        $laravel = new Technology();
        $laravel->setName('Laravel');
        $laravel->setCategory($scBackend);
        $laravel->setImageName('test-600x400.png');
        $laravel->setLevel(4);
        $manager->persist($laravel);

        $scFrontend = new SkillCategory();
        $scFrontend->setName('Frontend');
        $scFrontend->setDisplayOrder(2);
        $manager->persist($scFrontend);
        $javascript = new Technology();
        $javascript->setName('JavaScript');
        $javascript->setCategory($scFrontend);
        $javascript->setLevel(4);
        $javascript->setImageName('test-600x400.png');
        $manager->persist($javascript);
        $vuejs = new Technology();
        $vuejs->setName('Vue.js');
        $vuejs->setCategory($scFrontend);
        $vuejs->setLevel(4);
        $vuejs->setImageName('test-600x400.png');
        $manager->persist($vuejs);
        $tailwind = new Technology();
        $tailwind->setName('Tailwind CSS');
        $tailwind->setCategory($scFrontend);
        $tailwind->setLevel(5);
        $tailwind->setImageName('test-600x400.png');
        $manager->persist($tailwind);

        $scDatabases = new SkillCategory();
        $scDatabases->setName('Bases de Données');
        $scDatabases->setDisplayOrder(3);
        $manager->persist($scDatabases);
        $mysql = new Technology();
        $mysql->setName('MySQL');
        $mysql->setCategory($scDatabases);
        $mysql->setLevel(4);
        $mysql->setImageName('test-600x400.png');
        $manager->persist($mysql);
        $postgresql = new Technology();
        $postgresql->setName('PostgreSQL');
        $postgresql->setCategory($scDatabases);
        $postgresql->setLevel(3);
        $postgresql->setImageName('test-600x400.png');
        $manager->persist($postgresql);

        $scDevOps = new SkillCategory();
        $scDevOps->setName('DevOps & Outils');
        $scDevOps->setDisplayOrder(4);
        $manager->persist($scDevOps);
        $docker = new Technology();
        $docker->setName('Docker');
        $docker->setCategory($scDevOps);
        $docker->setLevel(4);
        $docker->setImageName('test-600x400.png');
        $manager->persist($docker);
        $git = new Technology();
        $git->setName('Git');
        $git->setCategory($scDevOps);
        $git->setLevel(5);
        $git->setImageName('test-600x400.png');
        $manager->persist($git);

        return [
            'php'        => $php,
            'symfony'    => $symfony,
            'laravel'    => $laravel,
            'javascript' => $javascript,
            'vuejs'      => $vuejs,
            'tailwind'   => $tailwind,
            'mysql'      => $mysql,
            'postgresql' => $postgresql,
            'docker'     => $docker,
            'git'        => $git,
        ];
    }

    private function loadProjects(ObjectManager $manager, array $technologies): void
    {
        $project1 = new Project();
        $project1->setTitle('Portfolio V1');
        $project1->setDescription(
            "Création d'un site portfolio personnel pour présenter mes compétences et réalisations. " .
            "Design moderne et responsive, avec une interface d'administration pour gérer le contenu."
        );
        $project1->setMainImageName('test-600x400.png');
        $project1->setStartDate(new \DateTime('2023-01-15'));
        $project1->setEndDate(new \DateTime('2023-03-01'));
        $project1->setUrl('https://mon-portfolio-exemple.com');
        $project1->setRepositoryUrl('https://github.com/user/portfolio-v1');
        $project1->setMainImageName('project_portfolio.jpg');
        $project1->addTechnology($technologies['symfony']);
        $project1->addTechnology($technologies['vuejs']);
        $project1->addTechnology($technologies['tailwind']);
        $project1->addTechnology($technologies['mysql']);
        $project1->addTechnology($technologies['docker']);
        $project1->setPublished(true);
        $manager->persist($project1);

        $p1Img1 = new ProjectImage();
        $p1Img1->setProject($project1);
        $p1Img1->setImageName('p1_gallery_1.jpg'); // User needs to place this
        $p1Img1->setAltText('Vue de la page d\'accueil du portfolio');
        $p1Img1->setDisplayOrder(1);
        $manager->persist($p1Img1);
        $project1->addProjectImage($p1Img1);

        $p1Img2 = new ProjectImage();
        $p1Img2->setProject($project1);
        $p1Img2->setImageName('p1_gallery_2.jpg'); // User needs to place this
        $p1Img2->setAltText('Interface d\'administration');
        $p1Img2->setDisplayOrder(2);
        $manager->persist($p1Img2);
        $project1->addProjectImage($p1Img2);

        $project2 = new Project();
        $project2->setTitle('Application E-commerce "ShopEasy"');
        $project2->setDescription(
            "Développement d'une plateforme e-commerce complète avec gestion de produits, paniers, " .
            "commandes et paiements. Intégration d'une API externe pour la gestion des stocks."
        );
        $project2->setMainImageName('test-600x400.png');
        $project2->setStartDate(new \DateTime('2022-06-01'));
        $project2->setEndDate(new \DateTime('2022-12-20'));
        $project2->setMainImageName('project_ecommerce.jpg');
        $project2->addTechnology($technologies['php']);
        $project2->addTechnology($technologies['laravel']);
        $project2->addTechnology($technologies['javascript']);
        $project2->addTechnology($technologies['postgresql']);
        $project2->setPublished(true);
        $manager->persist($project2);

        $p2Img1 = new ProjectImage();
        $p2Img1->setProject($project2);
        $p2Img1->setImageName('p2_gallery_1.jpg'); // User needs to place this
        $p2Img1->setAltText('Page produit ShopEasy');
        $p2Img1->setDisplayOrder(1);
        $manager->persist($p2Img1);
        $project2->addProjectImage($p2Img1);

        $project3 = new Project();
        $project3->setTitle('Outil de Gestion de Tâches "TaskMaster"');
        $project3->setDescription(
            "Une application web pour la gestion de tâches personnelles et d'équipe, avec des " .
            'fonctionnalités de priorisation, de suivi du temps et de collaboration.'
        );
        $project3->setMainImageName('test-600x400.png');
        $project3->setStartDate(new \DateTime('2024-01-10'));
        $project3->setMainImageName('project_taskmanager.jpg');
        $project3->addTechnology($technologies['symfony']);
        $project3->addTechnology($technologies['vuejs']);
        $project3->addTechnology($technologies['tailwind']);
        $project3->addTechnology($technologies['mysql']);
        $project3->setPublished(false); // Not yet published
        $manager->persist($project3);
    }

    private function loadEducation(ObjectManager $manager): void
    {
        $edu1 = new Education();
        $edu1->setDegree('Master en Ingénierie Logicielle');
        $edu1->setInstitution('Grande École d\'Informatique Imaginaire (GEII)');
        $edu1->setStartDate(new \DateTime('2018-09-01'));
        $edu1->setEndDate(new \DateTime('2020-07-15'));
        $edu1->setDescription(
            'Spécialisation en développement web et mobile. ' .
            'Projet de fin d\'études sur les architectures microservices.'
        );
        $manager->persist($edu1);

        $edu2 = new Education();
        $edu2->setDegree('Bootcamp Développement Web Full-Stack');
        $edu2->setInstitution('Le Wagon Coding School (ou équivalent)');
        $edu2->setStartDate(new \DateTime('2017-01-01'));
        $edu2->setEndDate(new \DateTime('2017-04-01'));
        $edu2->setDescription(
            'Formation intensive de 9 semaines couvrant Ruby on Rails, JavaScript, HTML, CSS, ' .
            'et les bonnes pratiques de développement.'
        );
        $manager->persist($edu2);
    }

    private function loadExperience(ObjectManager $manager): void
    {
        $exp1 = new Experience();
        $exp1->setJobTitle('Développeur Web Senior');
        $exp1->setCompany('Tech Solutions Inc.');
        $exp1->setStartDate(new \DateTime('2021-06-01'));
        $exp1->setIsCurrent(true); // Current job
        $exp1->setDescription(
            "Responsable du développement et de la maintenance d'applications web SaaS. " .
            'Mentorat de développeurs juniors. Veille technologique et proposition ' .
            "d'améliorations d'architecture."
        );
        $manager->persist($exp1);

        $exp2 = new Experience();
        $exp2->setJobTitle('Développeur Web Full-Stack');
        $exp2->setCompany('Innovatech Startup');
        $exp2->setStartDate(new \DateTime('2020-08-01'));
        $exp2->setEndDate(new \DateTime('2021-05-30'));
        $exp2->setDescription(
            "Participation à la création d'une nouvelle plateforme web de A à Z, du design " .
            'initial au déploiement. Utilisation de méthodes agiles (Scrum).'
        );
        $manager->persist($exp2);
    }

    private function loadHobbies(ObjectManager $manager): void
    {
        $hobby1 = new Hobby();
        $hobby1->setName('Randonnée en montagne');
        $hobby1->setIcon('fas fa-hiking'); // FontAwesome example
        $manager->persist($hobby1);

        $hobby2 = new Hobby();
        $hobby2->setName('Photographie');
        $hobby2->setIcon('fas fa-camera-retro');
        $manager->persist($hobby2);

        $hobby3 = new Hobby();
        $hobby3->setName('Jeux de stratégie');
        $hobby3->setIcon('fas fa-chess-knight');
        $manager->persist($hobby3);

        $hobby4 = new Hobby();
        $hobby4->setName('Lecture (Science-Fiction)');
        $hobby4->setIcon('fas fa-book-open');
        $manager->persist($hobby4);
    }

    private function loadArticles(ObjectManager $manager, User $adminUser, array $technologies): void
    {
        $article1 = new Article();
        $article1->setTitle('Comprendre le Dataloader Pattern avec Symfony UX');
        $article1->setSlug('comprendre-dataloader-pattern-symfony-ux');
        $article1->setContent(
            'Le Dataloader Pattern est une technique puissante pour optimiser les requêtes ' .
            'SQL/API dans les applications GraphQL, ' .
            'mais son principe peut être appliqué plus largement. ' .
            'Symfony UX offre des outils qui, combinés à Stimulus, ' .
            "permettent d'implémenter des chargements de données différés et groupés, '.
            'améliorant ainsi les performances perçues. \n\n" .
            'Cet article explore comment mettre en place ce pattern dans un contexte Symfony classique...'
        );
        $article1->setAuthor($adminUser);
        $article1->addTechnology($technologies['symfony']);
        $article1->addTechnology($technologies['javascript']);
        $article1->setIsPublished(true);
        $article1->setPublishedAt(new \DateTimeImmutable('-5 days'));
        $article1->setMainImageName('article_symfony_ux.jpg'); // User needs to place this image
        $manager->persist($article1);

        $article2 = new Article();
        $article2->setTitle('Les Nouveautés de PHP 8.3 à ne pas Manquer');
        $article2->setSlug('nouveautes-php-8-3');
        $article2->setContent(
            "PHP 8.3 arrive avec son lot d'améliorations et de nouvelles fonctionnalités. " .
            'Des types readonly pour les classes, ' .
            "la nouvelle fonction `json_validate()`, en passant par des améliorations de performance... \n\n" .
            'Découvrons ensemble les apports les plus significatifs de cette version ".
            "et comment ils peuvent impacter positivement vos projets.'
        );
        $article2->setMainImageName('test-600x400.png');
        $article2->setAuthor($adminUser);
        $article2->addTechnology($technologies['php']);
        $article2->setIsPublished(true);
        $article2->setPublishedAt(new \DateTimeImmutable('-15 days'));
        $manager->persist($article2);

        $article3 = new Article();
        $article3->setTitle('Introduction à Tailwind CSS pour les Développeurs Backend');
        $article3->setSlug('tailwind-css-pour-backend-devs');
        $article3->setContent(
            'Tailwind CSS est souvent perçu comme un outil purement frontend. ".
            "Cependant, sa philosophie utility-first peut grandement simplifier ' .
            'la vie des développeurs backend qui ont besoin de créer des interfaces rapidement ' .
            "sans se perdre dans du CSS complexe. \n\n" .
            'Cet article est un guide de démarrage rapide pour intégrer Tailwind dans vos projets Symfony ou Laravel...'
        );
        $article3->setMainImageName('test-600x400.png');
        $article3->setAuthor($adminUser);
        $article3->addTechnology($technologies['tailwind']);
        $article3->addTechnology($technologies['symfony']);
        $article3->setIsPublished(false); // Draft
        $article3->setPublishedAt(new \DateTimeImmutable('+10 days')); // Scheduled
        $manager->persist($article3);
    }

    private function loadSoftSkills(ObjectManager $manager): void
    {
        $ssCommunication = new SoftSkill();
        $ssCommunication->setName('Communication');

        $ssCommunication->setIcon('fas fa-comments');
        $ssCommunication->setDisplayOrder(1);
        $manager->persist($ssCommunication);

        $ssTeamwork = new SoftSkill();
        $ssTeamwork->setName('Travail d\'équipe');

        $ssTeamwork->setIcon('fas fa-users');
        $ssTeamwork->setDisplayOrder(2);
        $manager->persist($ssTeamwork);

        $ssProblemSolving = new SoftSkill();
        $ssProblemSolving->setName('Résolution de problèmes');

        $ssProblemSolving->setIcon('fas fa-lightbulb'); // or 'fas fa-puzzle-piece'
        $ssProblemSolving->setDisplayOrder(3);
        $manager->persist($ssProblemSolving);

        $ssCuriosity = new SoftSkill();
        $ssCuriosity->setName('Curiosité & Apprentissage continu');

        $ssCuriosity->setIcon('fas fa-search'); // or 'fas fa-book-reader'
        $ssCuriosity->setDisplayOrder(4);
        $manager->persist($ssCuriosity);

        $ssAdaptability = new SoftSkill();
        $ssAdaptability->setName('Adaptabilité');

        $ssAdaptability->setIcon('fas fa-cogs'); // or 'fas fa-random'
        $ssAdaptability->setDisplayOrder(5);
        $manager->persist($ssAdaptability);

        $ssCreativity = new SoftSkill();
        $ssCreativity->setName('Créativité');

        $ssCreativity->setIcon('fas fa-paint-brush');
        $ssCreativity->setDisplayOrder(6);
        $manager->persist($ssCreativity);
    }
}
