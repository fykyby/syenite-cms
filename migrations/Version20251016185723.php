<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251016185723 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__layout_data AS SELECT id, locale_id, name, data, theme FROM layout_data');
        $this->addSql('DROP TABLE layout_data');
        $this->addSql('CREATE TABLE layout_data (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, locale_id INTEGER NOT NULL, name VARCHAR(255) NOT NULL, data CLOB DEFAULT NULL --(DC2Type:json)
        , theme VARCHAR(255) NOT NULL, CONSTRAINT FK_42B0D9FAE559DFD1 FOREIGN KEY (locale_id) REFERENCES data_locale (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO layout_data (id, locale_id, name, data, theme) SELECT id, locale_id, name, data, theme FROM __temp__layout_data');
        $this->addSql('DROP TABLE __temp__layout_data');
        $this->addSql('CREATE INDEX IDX_42B0D9FAE559DFD1 ON layout_data (locale_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__media AS SELECT id, type, name, variants FROM media');
        $this->addSql('DROP TABLE media');
        $this->addSql('CREATE TABLE media (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, type VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, variants CLOB NOT NULL --(DC2Type:json)
        )');
        $this->addSql('INSERT INTO media (id, type, name, variants) SELECT id, type, name, variants FROM __temp__media');
        $this->addSql('DROP TABLE __temp__media');
        $this->addSql('CREATE TEMPORARY TABLE __temp__page AS SELECT id, locale_id, path, type, data, meta, layout_name FROM page');
        $this->addSql('DROP TABLE page');
        $this->addSql('CREATE TABLE page (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, locale_id INTEGER NOT NULL, path VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, data CLOB NOT NULL --(DC2Type:json)
        , meta CLOB NOT NULL --(DC2Type:json)
        , layout_name VARCHAR(255) DEFAULT NULL, CONSTRAINT FK_140AB620E559DFD1 FOREIGN KEY (locale_id) REFERENCES data_locale (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO page (id, locale_id, path, type, data, meta, layout_name) SELECT id, locale_id, path, type, data, meta, layout_name FROM __temp__page');
        $this->addSql('DROP TABLE __temp__page');
        $this->addSql('CREATE INDEX IDX_140AB620E559DFD1 ON page (locale_id)');
        $this->addSql('CREATE UNIQUE INDEX unique_path_per_locale ON page (path, locale_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__user AS SELECT id, email, roles, password FROM user');
        $this->addSql('DROP TABLE user');
        $this->addSql('CREATE TABLE user (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles CLOB NOT NULL --(DC2Type:json)
        , password VARCHAR(255) NOT NULL)');
        $this->addSql('INSERT INTO user (id, email, roles, password) SELECT id, email, roles, password FROM __temp__user');
        $this->addSql('DROP TABLE __temp__user');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL ON user (email)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__layout_data AS SELECT id, locale_id, name, data, theme FROM layout_data');
        $this->addSql('DROP TABLE layout_data');
        $this->addSql('CREATE TABLE layout_data (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, locale_id INTEGER NOT NULL, name VARCHAR(255) NOT NULL, data CLOB DEFAULT NULL --(DC2Type:json)
        , theme VARCHAR(255) NOT NULL, CONSTRAINT FK_42B0D9FAE559DFD1 FOREIGN KEY (locale_id) REFERENCES data_locale (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO layout_data (id, locale_id, name, data, theme) SELECT id, locale_id, name, data, theme FROM __temp__layout_data');
        $this->addSql('DROP TABLE __temp__layout_data');
        $this->addSql('CREATE INDEX IDX_42B0D9FAE559DFD1 ON layout_data (locale_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__media AS SELECT id, type, name, variants FROM media');
        $this->addSql('DROP TABLE media');
        $this->addSql('CREATE TABLE media (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, type VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, variants CLOB NOT NULL --(DC2Type:json)
        )');
        $this->addSql('INSERT INTO media (id, type, name, variants) SELECT id, type, name, variants FROM __temp__media');
        $this->addSql('DROP TABLE __temp__media');
        $this->addSql('CREATE TEMPORARY TABLE __temp__page AS SELECT id, locale_id, path, type, data, meta, layout_name FROM page');
        $this->addSql('DROP TABLE page');
        $this->addSql('CREATE TABLE page (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, locale_id INTEGER NOT NULL, path VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, data CLOB NOT NULL --(DC2Type:json)
        , meta CLOB NOT NULL --(DC2Type:json)
        , layout_name VARCHAR(255) DEFAULT NULL, CONSTRAINT FK_140AB620E559DFD1 FOREIGN KEY (locale_id) REFERENCES data_locale (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO page (id, locale_id, path, type, data, meta, layout_name) SELECT id, locale_id, path, type, data, meta, layout_name FROM __temp__page');
        $this->addSql('DROP TABLE __temp__page');
        $this->addSql('CREATE INDEX IDX_140AB620E559DFD1 ON page (locale_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__user AS SELECT id, email, roles, password FROM user');
        $this->addSql('DROP TABLE user');
        $this->addSql('CREATE TABLE user (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles CLOB NOT NULL --(DC2Type:json)
        , password VARCHAR(255) NOT NULL)');
        $this->addSql('INSERT INTO user (id, email, roles, password) SELECT id, email, roles, password FROM __temp__user');
        $this->addSql('DROP TABLE __temp__user');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL ON user (email)');
    }
}
