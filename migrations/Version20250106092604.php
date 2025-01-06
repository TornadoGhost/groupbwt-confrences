<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250106092604 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add index FULLTEXT to conference and report table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE conference ADD FULLTEXT(title)');
        $this->addSql('ALTER TABLE report ADD FULLTEXT(title)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE conference DROP INDEX title');
        $this->addSql('ALTER TABLE report DROP INDEX title');
    }
}
