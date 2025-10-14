<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251014003233 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__layout_data AS SELECT id, name, data, theme FROM layout_data');
        $this->addSql('DROP TABLE layout_data');
        $this->addSql('CREATE TABLE layout_data (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, locale_id INTEGER NOT NULL, name VARCHAR(255) NOT NULL, data CLOB DEFAULT NULL --(DC2Type:json)
        , theme VARCHAR(255) NOT NULL, CONSTRAINT FK_42B0D9FAE559DFD1 FOREIGN KEY (locale_id) REFERENCES data_locale (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO layout_data (id, name, data, theme) SELECT id, name, data, theme FROM __temp__layout_data');
        $this->addSql('DROP TABLE __temp__layout_data');
        $this->addSql('CREATE INDEX IDX_42B0D9FAE559DFD1 ON layout_data (locale_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__layout_data AS SELECT id, name, data, theme FROM layout_data');
        $this->addSql('DROP TABLE layout_data');
        $this->addSql('CREATE TABLE layout_data (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, data CLOB DEFAULT NULL --(DC2Type:json)
        , theme VARCHAR(255) NOT NULL)');
        $this->addSql('INSERT INTO layout_data (id, name, data, theme) SELECT id, name, data, theme FROM __temp__layout_data');
        $this->addSql('DROP TABLE __temp__layout_data');
    }
}
