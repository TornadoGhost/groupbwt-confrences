<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241213113636 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE conference CHANGE country country VARCHAR(30) NOT NULL');
        $this->addSql('ALTER TABLE type CHANGE name name VARCHAR(30) NOT NULL');
        $this->addSql('ALTER TABLE user CHANGE email email VARCHAR(100) NOT NULL, CHANGE country country VARCHAR(30) NOT NULL, CHANGE phone phone VARCHAR(20) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE conference CHANGE country country VARCHAR(100) NOT NULL');
        $this->addSql('ALTER TABLE type CHANGE name name VARCHAR(50) NOT NULL');
        $this->addSql('ALTER TABLE user CHANGE email email VARCHAR(180) NOT NULL, CHANGE country country VARCHAR(255) NOT NULL, CHANGE phone phone VARCHAR(255) NOT NULL');
    }
}