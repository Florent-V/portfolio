<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260703212623 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add format (html/markdown) column to article_content, defaulting existing rows to html';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE article_content ADD format VARCHAR(255) NOT NULL DEFAULT 'html'");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE article_content DROP format');
    }
}
