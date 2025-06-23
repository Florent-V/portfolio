<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250331173805 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE ext_log_entries (id INT AUTO_INCREMENT NOT NULL, action VARCHAR(8) NOT NULL, logged_at DATETIME NOT NULL, object_id VARCHAR(64) DEFAULT NULL, object_class VARCHAR(191) NOT NULL, version INT NOT NULL, data LONGTEXT DEFAULT NULL COMMENT '(DC2Type:array)', username VARCHAR(191) DEFAULT NULL, INDEX log_class_lookup_idx (object_class), INDEX log_date_lookup_idx (logged_at), INDEX log_user_lookup_idx (username), INDEX log_version_lookup_idx (object_id, object_class, version), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB ROW_FORMAT = DYNAMIC
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE product ADD created_by INT DEFAULT NULL, ADD updated_by INT DEFAULT NULL, ADD created_at DATETIME NOT NULL, ADD deleted_at DATETIME DEFAULT NULL, CHANGE updated_at updated_at DATETIME NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE product ADD CONSTRAINT FK_D34A04ADDE12AB56 FOREIGN KEY (created_by) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE product ADD CONSTRAINT FK_D34A04AD16FE72E1 FOREIGN KEY (updated_by) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_D34A04ADDE12AB56 ON product (created_by)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_D34A04AD16FE72E1 ON product (updated_by)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user CHANGE first_name first_name VARCHAR(255) DEFAULT NULL, CHANGE last_name last_name VARCHAR(255) DEFAULT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            DROP TABLE ext_log_entries
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user CHANGE first_name first_name VARCHAR(255) NOT NULL, CHANGE last_name last_name VARCHAR(255) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE product DROP FOREIGN KEY FK_D34A04ADDE12AB56
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE product DROP FOREIGN KEY FK_D34A04AD16FE72E1
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_D34A04ADDE12AB56 ON product
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_D34A04AD16FE72E1 ON product
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE product DROP created_by, DROP updated_by, DROP created_at, DROP deleted_at, CHANGE updated_at updated_at DATETIME DEFAULT NULL
        SQL);
    }
}
