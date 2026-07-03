<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\AboutMe;
use App\Entity\Article;
use App\Entity\ArticleContent;
use App\Entity\Project;
use App\Entity\ProjectImage;
use App\Enum\ArticleContentType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpKernel\KernelInterface;

#[AsCommand(
    name: 'app:cleanup-orphaned-images',
    description: 'Remove orphaned image files and nullify missing image references in DB',
)]
class CleanupOrphanedImagesCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly KernelInterface $kernel,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('force', null, InputOption::VALUE_NONE, 'Apply changes (default is dry-run)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io    = new SymfonyStyle($input, $output);
        $force = (bool) $input->getOption('force');
        $root  = $this->kernel->getProjectDir() . '/public';

        if (!$force) {
            $io->note('DRY-RUN mode — pass --force to apply changes');
        }

        $deletedFiles  = 0;
        $nullifiedRows = 0;
        $deletedRows   = 0;

        // ── 1. Collect all known filenames per directory ────────────────────

        $knownFiles = [
            'uploads/images/projects'         => [],
            'uploads/images/projects/gallery' => [],
            'uploads/images/articles'         => [],
            'uploads/images/about_me'         => [],
        ];

        foreach ($this->em->getRepository(Project::class)->findAll() as $entity) {
            if ($fn = $entity->getMainImageName()) {
                $knownFiles['uploads/images/projects'][$fn] = true;
            }
        }

        foreach ($this->em->getRepository(ProjectImage::class)->findAll() as $entity) {
            if ($fn = $entity->getImageName()) {
                $knownFiles['uploads/images/projects/gallery'][$fn] = true;
            }
        }

        foreach ($this->em->getRepository(Article::class)->findAll() as $entity) {
            if ($fn = $entity->getMainImageName()) {
                $knownFiles['uploads/images/articles'][$fn] = true;
            }
        }

        foreach ($this->em->getRepository(ArticleContent::class)->findAll() as $entity) {
            if (ArticleContentType::IMAGE === $entity->getType() && ($fn = $entity->getImageName())) {
                $knownFiles['uploads/images/articles'][$fn] = true;
            }
        }

        foreach ($this->em->getRepository(AboutMe::class)->findAll() as $entity) {
            if ($fn = $entity->getProfilePictureName()) {
                $knownFiles['uploads/images/about_me'][$fn] = true;
            }
        }

        // ── 2. Files on disk not in DB → delete ─────────────────────────────

        $io->section('Orphaned files (on disk, not in DB)');

        foreach ($knownFiles as $relDir => $known) {
            $absDir = $root . '/' . $relDir;
            if (!is_dir($absDir)) {
                continue;
            }

            foreach (new \DirectoryIterator($absDir) as $file) {
                if ($file->isDot() || $file->isDir()) {
                    continue;
                }

                $name = $file->getFilename();
                if (!isset($known[$name])) {
                    $io->text(sprintf('  [DELETE FILE] %s/%s', $relDir, $name));
                    if ($force) {
                        unlink($file->getPathname());
                    }
                    ++$deletedFiles;
                }
            }
        }

        if (0 === $deletedFiles) {
            $io->text('  None.');
        }

        // ── 3. DB entries pointing to missing files ──────────────────────────

        $io->section('DB entries with missing files (will nullify or delete row)');

        // Project: nullify main image
        foreach ($this->em->getRepository(Project::class)->findAll() as $entity) {
            $fn = $entity->getMainImageName();
            if (null === $fn) {
                continue;
            }
            $path = $root . '/uploads/images/projects/' . $fn;
            if (!file_exists($path)) {
                $io->text(sprintf('  [NULLIFY] Project#%d main_image_name = %s', $entity->getId(), $fn));
                if ($force) {
                    $entity->setMainImageName(null);
                    $entity->setMainImageWidth(null);
                    $entity->setMainImageHeight(null);
                }
                ++$nullifiedRows;
            }
        }

        // Article: nullify main image
        foreach ($this->em->getRepository(Article::class)->findAll() as $entity) {
            $fn = $entity->getMainImageName();
            if (null === $fn) {
                continue;
            }
            $path = $root . '/uploads/images/articles/' . $fn;
            if (!file_exists($path)) {
                $io->text(sprintf('  [NULLIFY] Article#%d main_image_name = %s', $entity->getId(), $fn));
                if ($force) {
                    $entity->setMainImageName(null);
                    $entity->setMainImageWidth(null);
                    $entity->setMainImageHeight(null);
                }
                ++$nullifiedRows;
            }
        }

        // ArticleContent (image type): delete row (child entity, safe)
        foreach ($this->em->getRepository(ArticleContent::class)->findAll() as $entity) {
            if (ArticleContentType::IMAGE !== $entity->getType()) {
                continue;
            }
            $fn = $entity->getImageName();
            if (null === $fn) {
                continue;
            }
            $path = $root . '/uploads/images/articles/' . $fn;
            if (!file_exists($path)) {
                $io->text(sprintf(
                    '  [DELETE ROW] ArticleContent#%d (article#%d) image_name = %s',
                    $entity->getId(),
                    $entity->getArticle()?->getId(),
                    $fn
                ));
                if ($force) {
                    $this->em->remove($entity);
                }
                ++$deletedRows;
            }
        }

        // ProjectImage: delete row (child entity, safe)
        foreach ($this->em->getRepository(ProjectImage::class)->findAll() as $entity) {
            $fn = $entity->getImageName();
            if (null === $fn) {
                continue;
            }
            $path = $root . '/uploads/images/projects/gallery/' . $fn;
            if (!file_exists($path)) {
                $io->text(sprintf(
                    '  [DELETE ROW] ProjectImage#%d (project#%d) image_name = %s',
                    $entity->getId(),
                    $entity->getProject()?->getId(),
                    $fn
                ));
                if ($force) {
                    $this->em->remove($entity);
                }
                ++$deletedRows;
            }
        }

        // AboutMe: nullify profile picture
        foreach ($this->em->getRepository(AboutMe::class)->findAll() as $entity) {
            $fn = $entity->getProfilePictureName();
            if (null === $fn) {
                continue;
            }
            $path = $root . '/uploads/images/about_me/' . $fn;
            if (!file_exists($path)) {
                $io->text(sprintf('  [NULLIFY] AboutMe#%d profile_picture_name = %s', $entity->getId(), $fn));
                if ($force) {
                    $entity->setProfilePictureName(null);
                    $entity->setProfilePictureWidth(null);
                    $entity->setProfilePictureHeight(null);
                }
                ++$nullifiedRows;
            }
        }

        if (0 === $nullifiedRows && 0 === $deletedRows) {
            $io->text('  None.');
        }

        // ── 4. Flush & summary ───────────────────────────────────────────────

        if ($force) {
            $this->em->flush();
        }

        $io->success(sprintf(
            '%s — files deleted: %d | DB rows nullified: %d | DB rows deleted: %d',
            $force ? 'DONE' : 'DRY-RUN',
            $deletedFiles,
            $nullifiedRows,
            $deletedRows,
        ));

        return Command::SUCCESS;
    }
}
