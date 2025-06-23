<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250411175103 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE oauth_account (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, provider VARCHAR(255) NOT NULL, provider_id VARCHAR(255) NOT NULL, INDEX IDX_6E30F9D1A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE oauth_account ADD CONSTRAINT FK_6E30F9D1A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user DROP oauth_provider
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE oauth_account DROP FOREIGN KEY FK_6E30F9D1A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE oauth_account
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user ADD oauth_provider VARCHAR(255) DEFAULT NULL
        SQL);
    }
}
