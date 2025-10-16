<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251016190209 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__page AS SELECT id, locale_id, path, type, data, meta FROM page');
        $this->addSql('DROP TABLE page');
        $this->addSql('CREATE TABLE page (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, locale_id INTEGER NOT NULL, layout_data_id INTEGER DEFAULT NULL, path VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, data CLOB NOT NULL --(DC2Type:json)
        , meta CLOB NOT NULL --(DC2Type:json)
        , CONSTRAINT FK_140AB620E559DFD1 FOREIGN KEY (locale_id) REFERENCES data_locale (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_140AB6204589FEAE FOREIGN KEY (layout_data_id) REFERENCES layout_data (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO page (id, locale_id, path, type, data, meta) SELECT id, locale_id, path, type, data, meta FROM __temp__page');
        $this->addSql('DROP TABLE __temp__page');
        $this->addSql('CREATE UNIQUE INDEX unique_path_per_locale ON page (path, locale_id)');
        $this->addSql('CREATE INDEX IDX_140AB620E559DFD1 ON page (locale_id)');
        $this->addSql('CREATE INDEX IDX_140AB6204589FEAE ON page (layout_data_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__page AS SELECT id, locale_id, path, type, data, meta FROM page');
        $this->addSql('DROP TABLE page');
        $this->addSql('CREATE TABLE page (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, locale_id INTEGER NOT NULL, path VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, data CLOB NOT NULL --(DC2Type:json)
        , meta CLOB NOT NULL --(DC2Type:json)
        , layout_name VARCHAR(255) DEFAULT NULL, CONSTRAINT FK_140AB620E559DFD1 FOREIGN KEY (locale_id) REFERENCES data_locale (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO page (id, locale_id, path, type, data, meta) SELECT id, locale_id, path, type, data, meta FROM __temp__page');
        $this->addSql('DROP TABLE __temp__page');
        $this->addSql('CREATE INDEX IDX_140AB620E559DFD1 ON page (locale_id)');
        $this->addSql('CREATE UNIQUE INDEX unique_path_per_locale ON page (path, locale_id)');
    }
}
