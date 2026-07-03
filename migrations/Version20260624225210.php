<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260624225210 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE project_tag (project_id INT NOT NULL, tag_id INT NOT NULL, INDEX IDX_91F26D60166D1F9C (project_id), INDEX IDX_91F26D60BAD26311 (tag_id), PRIMARY KEY(project_id, tag_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE project_tag ADD CONSTRAINT FK_91F26D60166D1F9C FOREIGN KEY (project_id) REFERENCES project (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE project_tag ADD CONSTRAINT FK_91F26D60BAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE about_me ADD profile_picture_width INT DEFAULT NULL, ADD profile_picture_height INT DEFAULT NULL');
        $this->addSql('ALTER TABLE article ADD main_image_width INT DEFAULT NULL, ADD main_image_height INT DEFAULT NULL');
        $this->addSql('ALTER TABLE project ADD main_image_width INT DEFAULT NULL, ADD main_image_height INT DEFAULT NULL');
        $this->addSql('ALTER TABLE project_image ADD image_width INT DEFAULT NULL, ADD image_height INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE project_tag DROP FOREIGN KEY FK_91F26D60166D1F9C');
        $this->addSql('ALTER TABLE project_tag DROP FOREIGN KEY FK_91F26D60BAD26311');
        $this->addSql('DROP TABLE project_tag');
        $this->addSql('ALTER TABLE about_me DROP profile_picture_width, DROP profile_picture_height');
        $this->addSql('ALTER TABLE article DROP main_image_width, DROP main_image_height');
        $this->addSql('ALTER TABLE project DROP main_image_width, DROP main_image_height');
        $this->addSql('ALTER TABLE project_image DROP image_width, DROP image_height');
    }
}
