<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251026020544 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__redirect AS SELECT id, from_path, to_path FROM redirect');
        $this->addSql('DROP TABLE redirect');
        $this->addSql('CREATE TABLE redirect (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, locale_id INTEGER NOT NULL, from_path VARCHAR(255) NOT NULL, to_path VARCHAR(255) NOT NULL, CONSTRAINT FK_C30C9E2BE559DFD1 FOREIGN KEY (locale_id) REFERENCES data_locale (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO redirect (id, from_path, to_path) SELECT id, from_path, to_path FROM __temp__redirect');
        $this->addSql('DROP TABLE __temp__redirect');
        $this->addSql('CREATE INDEX IDX_C30C9E2BE559DFD1 ON redirect (locale_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__redirect AS SELECT id, from_path, to_path FROM redirect');
        $this->addSql('DROP TABLE redirect');
        $this->addSql('CREATE TABLE redirect (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, from_path VARCHAR(255) NOT NULL, to_path VARCHAR(255) NOT NULL)');
        $this->addSql('INSERT INTO redirect (id, from_path, to_path) SELECT id, from_path, to_path FROM __temp__redirect');
        $this->addSql('DROP TABLE __temp__redirect');
    }
}
