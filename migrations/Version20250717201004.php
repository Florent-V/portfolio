<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250717201004 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE social (id INT AUTO_INCREMENT NOT NULL, about_me_id INT NOT NULL, created_by INT DEFAULT NULL, updated_by INT DEFAULT NULL, name VARCHAR(255) NOT NULL, url VARCHAR(500) NOT NULL, icon VARCHAR(255) NOT NULL, sort_order INT DEFAULT NULL, is_active TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, deleted_at DATETIME DEFAULT NULL, INDEX IDX_7161E18759001B2D (about_me_id), INDEX IDX_7161E187DE12AB56 (created_by), INDEX IDX_7161E18716FE72E1 (updated_by), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE social ADD CONSTRAINT FK_7161E18759001B2D FOREIGN KEY (about_me_id) REFERENCES about_me (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE social ADD CONSTRAINT FK_7161E187DE12AB56 FOREIGN KEY (created_by) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE social ADD CONSTRAINT FK_7161E18716FE72E1 FOREIGN KEY (updated_by) REFERENCES user (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE social DROP FOREIGN KEY FK_7161E18759001B2D
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE social DROP FOREIGN KEY FK_7161E187DE12AB56
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE social DROP FOREIGN KEY FK_7161E18716FE72E1
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE social
        SQL);
    }
}
