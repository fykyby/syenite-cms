<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251013123259 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__page AS SELECT id, path, type, data, meta, layout_name FROM page');
        $this->addSql('DROP TABLE page');
        $this->addSql('CREATE TABLE page (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, locale_id INTEGER NOT NULL, path VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, data CLOB NOT NULL --(DC2Type:json)
        , meta CLOB NOT NULL --(DC2Type:json)
        , layout_name VARCHAR(255) DEFAULT NULL, CONSTRAINT FK_140AB620E559DFD1 FOREIGN KEY (locale_id) REFERENCES data_locale (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO page (id, path, type, data, meta, layout_name) SELECT id, path, type, data, meta, layout_name FROM __temp__page');
        $this->addSql('DROP TABLE __temp__page');
        $this->addSql('CREATE INDEX IDX_140AB620E559DFD1 ON page (locale_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__page AS SELECT id, path, type, data, meta, layout_name FROM page');
        $this->addSql('DROP TABLE page');
        $this->addSql('CREATE TABLE page (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, path VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, data CLOB NOT NULL --(DC2Type:json)
        , meta CLOB NOT NULL --(DC2Type:json)
        , layout_name VARCHAR(255) DEFAULT NULL)');
        $this->addSql('INSERT INTO page (id, path, type, data, meta, layout_name) SELECT id, path, type, data, meta, layout_name FROM __temp__page');
        $this->addSql('DROP TABLE __temp__page');
    }
}
