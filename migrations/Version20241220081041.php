<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241220081041 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Added table report and report_comment, also added deleted_at for user table';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE report (id INT AUTO_INCREMENT NOT NULL, conference_id INT NOT NULL, title VARCHAR(255) NOT NULL, started_at DATETIME NOT NULL, ended_at DATETIME NOT NULL, description TEXT, document VARCHAR(100) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, deleted_at DATETIME DEFAULT NULL, INDEX IDX_C42F7784604B8382 (conference_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE report_comment (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, report_id INT DEFAULT NULL, content TEXT, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, deleted_at DATETIME DEFAULT NULL, INDEX IDX_F4ED2F6CA76ED395 (user_id), INDEX IDX_F4ED2F6C4BD2A4C0 (report_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE report ADD CONSTRAINT FK_C42F7784604B8382 FOREIGN KEY (conference_id) REFERENCES conference (id)');
        $this->addSql('ALTER TABLE report_comment ADD CONSTRAINT FK_F4ED2F6CA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE report_comment ADD CONSTRAINT FK_F4ED2F6C4BD2A4C0 FOREIGN KEY (report_id) REFERENCES report (id)');
        $this->addSql('ALTER TABLE user ADD deleted_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE report DROP FOREIGN KEY FK_C42F7784604B8382');
        $this->addSql('ALTER TABLE report_comment DROP FOREIGN KEY FK_F4ED2F6CA76ED395');
        $this->addSql('ALTER TABLE report_comment DROP FOREIGN KEY FK_F4ED2F6C4BD2A4C0');
        $this->addSql('DROP TABLE report');
        $this->addSql('DROP TABLE report_comment');
        $this->addSql('ALTER TABLE user DROP deleted_at');
    }
}
