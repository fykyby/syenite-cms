<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251013233815 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__data_locale AS SELECT id, name, domain, is_default FROM data_locale');
        $this->addSql('DROP TABLE data_locale');
        $this->addSql('CREATE TABLE data_locale (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, domain VARCHAR(255) DEFAULT NULL, is_default BOOLEAN NOT NULL)');
        $this->addSql('INSERT INTO data_locale (id, name, domain, is_default) SELECT id, name, domain, is_default FROM __temp__data_locale');
        $this->addSql('DROP TABLE __temp__data_locale');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_217EF781A7A91E0B ON data_locale (domain)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_217EF7815E237E06 ON data_locale (name)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__data_locale AS SELECT id, name, domain, is_default FROM data_locale');
        $this->addSql('DROP TABLE data_locale');
        $this->addSql('CREATE TABLE data_locale (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, domain VARCHAR(255) DEFAULT NULL, is_default BOOLEAN NOT NULL, code VARCHAR(5) NOT NULL)');
        $this->addSql('INSERT INTO data_locale (id, name, domain, is_default) SELECT id, name, domain, is_default FROM __temp__data_locale');
        $this->addSql('DROP TABLE __temp__data_locale');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_217EF7815E237E06 ON data_locale (name)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_217EF781A7A91E0B ON data_locale (domain)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_217EF78177153098 ON data_locale (code)');
    }
}
