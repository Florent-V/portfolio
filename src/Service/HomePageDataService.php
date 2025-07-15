<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\AboutMeRepository;
use App\Repository\ArticleRepository;
use App\Repository\EducationRepository;
use App\Repository\ExperienceRepository;
use App\Repository\HobbyRepository;
use App\Repository\ProjectRepository;
use App\Repository\SkillCategoryRepository;
use App\Repository\SoftSkillRepository;
use App\Repository\TechnologyRepository;
use App\Repository\UserRepository;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

readonly class HomePageDataService
{
    /**
     * @SuppressWarnings("PHPMD.ExcessiveParameterList")
     */
    public function __construct(
        private UserRepository $userRepository,
        private ProjectRepository $projectRepository,
        private TechnologyRepository $technologyRepository,
        private SkillCategoryRepository $skillCategoryRepository,
        private SoftSkillRepository $softSkillRepository,
        private ArticleRepository $articleRepository,
        private AboutMeRepository $aboutMeRepository,
        private EducationRepository $educationRepository,
        private ExperienceRepository $experienceRepository,
        private HobbyRepository $hobbyRepository,
        private SerializerInterface&NormalizerInterface $serializer,
    ) {
    }

    /**
     * @throws ExceptionInterface
     *
     * @return array<string, mixed>
     */
    public function getDataForHomepage(): array
    {
        $softSkills = $this->softSkillRepository->findAllOrdered();

        return [
            'user'     => $this->userRepository->find(1),
            'aboutMe'  => $this->aboutMeRepository->findOneBy([]),
            'projects' => $this->projectRepository->findBy(
                ['published' => true],
                ['startDate' => 'DESC'],
                6
            ),
            'technologies'    => $this->technologyRepository->findAll(),
            'skillCategories' => $this->skillCategoryRepository->findBy(
                [],
                ['displayOrder' => 'ASC', 'name' => 'ASC']
            ),
            'softSkills' => $softSkills,
            'educations' => $this->educationRepository->findBy(
                [],
                ['startDate' => 'DESC']
            ),
            'experiences' => $this->experienceRepository->findBy(
                [],
                ['startDate' => 'DESC']
            ),
            'hobbies' => $this->hobbyRepository->findBy(
                [],
                ['name' => 'ASC']
            ),
            'articles' => $this->articleRepository->findBy(
                ['isPublished' => true],
                ['createdAt' => 'DESC'],
                3
            ),
            'softSkillsForVue' => $this->serializer->normalize(
                $softSkills,
                null,
                ['groups' => 'softSkills:show']
            ),
        ];
    }
}
