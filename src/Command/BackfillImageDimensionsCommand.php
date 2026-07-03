<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\AboutMe;
use App\Entity\Article;
use App\Entity\Project;
use App\Entity\ProjectImage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpKernel\KernelInterface;

#[AsCommand(
    name: 'app:backfill-image-dimensions',
    description: 'Backfill width/height for existing uploaded images',
)]
class BackfillImageDimensionsCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly KernelInterface $kernel,
    ) {
        parent::__construct();
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output,
    ): int {
        $io      = new SymfonyStyle($input, $output);
        $updated = 0;
        $skipped = 0;

        $map = [
            [
                'entities' => $this->em->getRepository(Project::class)->findAll(),
                'getFile'  => fn (Project $e): ?string => $e->getMainImageName(),
                'dir'      => 'uploads/images/projects',
                'setSize'  => fn (Project $e, int $w, int $h) => $e->setMainImageWidth($w)->setMainImageHeight($h),
                'hasSize'  => fn (Project $e): bool => null !== $e->getMainImageWidth(),
            ],
            [
                'entities' => $this->em->getRepository(Article::class)->findAll(),
                'getFile'  => fn (Article $e): ?string => $e->getMainImageName(),
                'dir'      => 'uploads/images/articles',
                'setSize'  => fn (Article $e, int $w, int $h) => $e->setMainImageWidth($w)->setMainImageHeight($h),
                'hasSize'  => fn (Article $e): bool => null !== $e->getMainImageWidth(),
            ],
            [
                'entities' => $this->em->getRepository(ProjectImage::class)->findAll(),
                'getFile'  => fn (ProjectImage $e): ?string => $e->getImageName(),
                'dir'      => 'uploads/images/projects/gallery',
                'setSize'  => fn (ProjectImage $e, int $w, int $h) => $e->setImageWidth($w)->setImageHeight($h),
                'hasSize'  => fn (ProjectImage $e): bool => null !== $e->getImageWidth(),
            ],
            [
                'entities' => $this->em->getRepository(AboutMe::class)->findAll(),
                'getFile'  => fn (AboutMe $e): ?string => $e->getProfilePictureName(),
                'dir'      => 'uploads/images/about_me',
                'setSize'  => fn (AboutMe $e, int $w, int $h) => $e->setProfilePictureWidth($w)
                                                                    ->setProfilePictureHeight($h),
                'hasSize' => fn (AboutMe $e): bool => null !== $e->getProfilePictureWidth(),
            ],
        ];

        foreach ($map as $config) {
            foreach ($config['entities'] as $entity) {
                if ($config['hasSize']($entity)) {
                    ++$skipped;
                    continue;
                }

                $fileName = $config['getFile']($entity);
                if (null === $fileName) {
                    ++$skipped;
                    continue;
                }

                $path = $this->kernel->getProjectDir() . '/public/' . $config['dir'] . '/' . $fileName;
                if (!file_exists($path)) {
                    $io->warning(sprintf('File not found: %s', $path));
                    ++$skipped;
                    continue;
                }

                $size = getimagesize($path);
                if (false === $size) {
                    $io->warning(sprintf('Not a valid image: %s', $path));
                    ++$skipped;
                    continue;
                }

                $config['setSize']($entity, $size[0], $size[1]);
                ++$updated;
                $io->text(sprintf('  ✓ %s → %dx%d', basename($path), $size[0], $size[1]));
            }
        }

        $this->em->flush();

        $io->success(sprintf('Done: %d updated, %d skipped.', $updated, $skipped));

        return Command::SUCCESS;
    }
}
