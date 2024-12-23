<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241220111630 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE conference ADD ended_at DATETIME DEFAULT NULL, CHANGE start started_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE report CHANGE description description TEXT');
        $this->addSql('ALTER TABLE report_comment CHANGE content content TEXT');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE report CHANGE description description TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE conference DROP ended_at, CHANGE started_at start DATETIME NOT NULL');
        $this->addSql('ALTER TABLE report_comment CHANGE content content TEXT DEFAULT NULL');
    }
}
