<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260530213402 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE ai_generation_log (id INT AUTO_INCREMENT NOT NULL, article_id INT DEFAULT NULL, author_id INT DEFAULT NULL, action VARCHAR(32) NOT NULL, provider VARCHAR(64) NOT NULL, model VARCHAR(128) NOT NULL, system_prompt LONGTEXT NOT NULL, user_prompt LONGTEXT NOT NULL, raw_response LONGTEXT NULL, duration_ms INT NOT NULL, prompt_tokens INT DEFAULT NULL, completion_tokens INT DEFAULT NULL, total_tokens INT DEFAULT NULL, cached_tokens INT DEFAULT NULL, success TINYINT(1) NOT NULL, error_message LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_AA8B8C507294869C (article_id), INDEX IDX_AA8B8C50F675F31B (author_id), INDEX IDX_AA8B8C5092C4739C8B8E8428 (provider, created_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE ai_generation_log ADD CONSTRAINT FK_AA8B8C507294869C FOREIGN KEY (article_id) REFERENCES article (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE ai_generation_log ADD CONSTRAINT FK_AA8B8C50F675F31B FOREIGN KEY (author_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('DROP INDEX IDX_75EA56E0FB7336F0 ON messenger_messages');
        $this->addSql('DROP INDEX IDX_75EA56E0E3BD61CE ON messenger_messages');
        $this->addSql('DROP INDEX IDX_75EA56E016BA31DB ON messenger_messages');
        $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 ON messenger_messages (queue_name, available_at, delivered_at, id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE ai_generation_log DROP FOREIGN KEY FK_AA8B8C507294869C');
        $this->addSql('ALTER TABLE ai_generation_log DROP FOREIGN KEY FK_AA8B8C50F675F31B');
        $this->addSql('DROP TABLE ai_generation_log');
        $this->addSql('DROP INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 ON messenger_messages');
        $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0 ON messenger_messages (queue_name)');
        $this->addSql('CREATE INDEX IDX_75EA56E0E3BD61CE ON messenger_messages (available_at)');
        $this->addSql('CREATE INDEX IDX_75EA56E016BA31DB ON messenger_messages (delivered_at)');
    }
}
