<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251012231838 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__data_locale AS SELECT id, name, code, domain FROM data_locale');
        $this->addSql('DROP TABLE data_locale');
        $this->addSql('CREATE TABLE data_locale (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, code VARCHAR(5) NOT NULL, domain VARCHAR(255) DEFAULT NULL)');
        $this->addSql('INSERT INTO data_locale (id, name, code, domain) SELECT id, name, code, domain FROM __temp__data_locale');
        $this->addSql('DROP TABLE __temp__data_locale');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_217EF7815E237E06 ON data_locale (name)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_217EF78177153098 ON data_locale (code)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_217EF781A7A91E0B ON data_locale (domain)');
        $this->addSql('ALTER TABLE settings ADD COLUMN default_locale VARCHAR(5) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__data_locale AS SELECT id, name, code, domain FROM data_locale');
        $this->addSql('DROP TABLE data_locale');
        $this->addSql('CREATE TABLE data_locale (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, code VARCHAR(5) NOT NULL, domain VARCHAR(255) DEFAULT NULL)');
        $this->addSql('INSERT INTO data_locale (id, name, code, domain) SELECT id, name, code, domain FROM __temp__data_locale');
        $this->addSql('DROP TABLE __temp__data_locale');
        $this->addSql('CREATE TEMPORARY TABLE __temp__settings AS SELECT id, email_account_username, email_account_password, email_account_host, email_account_port, current_theme FROM settings');
        $this->addSql('DROP TABLE settings');
        $this->addSql('CREATE TABLE settings (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, email_account_username VARCHAR(255) DEFAULT NULL, email_account_password VARCHAR(255) DEFAULT NULL, email_account_host VARCHAR(255) DEFAULT NULL, email_account_port VARCHAR(255) DEFAULT NULL, current_theme VARCHAR(255) NOT NULL)');
        $this->addSql('INSERT INTO settings (id, email_account_username, email_account_password, email_account_host, email_account_port, current_theme) SELECT id, email_account_username, email_account_password, email_account_host, email_account_port, current_theme FROM __temp__settings');
        $this->addSql('DROP TABLE __temp__settings');
    }
}
