<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260911203637 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE contact_message_log (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, subject VARCHAR(255) NOT NULL, message LONGTEXT NOT NULL, ip_address VARCHAR(45) DEFAULT NULL, user_agent LONGTEXT DEFAULT NULL, referer VARCHAR(1024) DEFAULT NULL, accept_language VARCHAR(255) DEFAULT NULL, forwarded_for VARCHAR(255) DEFAULT NULL, request_headers JSON DEFAULT NULL, blocked TINYINT(1) NOT NULL, block_reason VARCHAR(32) DEFAULT NULL, mail_sent TINYINT(1) NOT NULL, geo_lookup_status VARCHAR(32) DEFAULT NULL, country_code VARCHAR(8) DEFAULT NULL, country VARCHAR(128) DEFAULT NULL, region VARCHAR(128) DEFAULT NULL, city VARCHAR(128) DEFAULT NULL, isp VARCHAR(255) DEFAULT NULL, org VARCHAR(255) DEFAULT NULL, asn VARCHAR(64) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_C8A65D9722FFD58C8B8E8428 (ip_address, created_at), INDEX IDX_C8A65D97DA55EB80 (blocked), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE contact_message_log');
    }
}
