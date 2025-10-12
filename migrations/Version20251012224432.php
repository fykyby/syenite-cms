<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251012224432 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE layout_data ADD COLUMN theme VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__layout_data AS SELECT id, name, data FROM layout_data');
        $this->addSql('DROP TABLE layout_data');
        $this->addSql('CREATE TABLE layout_data (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, data CLOB DEFAULT NULL --(DC2Type:json)
        )');
        $this->addSql('INSERT INTO layout_data (id, name, data) SELECT id, name, data FROM __temp__layout_data');
        $this->addSql('DROP TABLE __temp__layout_data');
    }
}
