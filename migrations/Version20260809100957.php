<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260809100957 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add format (html/markdown) column to project, defaulting existing rows to html';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE project ADD format VARCHAR(255) NOT NULL DEFAULT 'html'");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE project DROP format');
    }
}
