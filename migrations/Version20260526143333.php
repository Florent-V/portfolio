<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260526143333 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE article_content (id INT AUTO_INCREMENT NOT NULL, article_id INT NOT NULL, type VARCHAR(255) NOT NULL, content LONGTEXT DEFAULT NULL, display_order INT NOT NULL, image_name VARCHAR(255) DEFAULT NULL, alt_text VARCHAR(255) DEFAULT NULL, language VARCHAR(100) DEFAULT NULL, updated_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_1317741E7294869C (article_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE article_content ADD CONSTRAINT FK_1317741E7294869C FOREIGN KEY (article_id) REFERENCES article (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE article DROP content
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE article_content DROP FOREIGN KEY FK_1317741E7294869C
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE article_content
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE article ADD content LONGTEXT NOT NULL
        SQL);
    }
}
