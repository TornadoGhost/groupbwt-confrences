<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241219152447 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Unsubscribe all users from conferences';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('TRUNCATE TABLE user_conference');

    }

    public function down(Schema $schema): void
    {

    }
}
