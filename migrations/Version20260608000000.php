<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260608000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add slug field to project table for SEO-friendly URLs';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE project ADD slug VARCHAR(255) DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_2FB3D0EE989D9B62 ON project (slug)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX UNIQ_2FB3D0EE989D9B62 ON project');
        $this->addSql('ALTER TABLE project DROP slug');
    }
}
