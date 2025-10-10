<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251010181212 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE page ADD COLUMN layout_name VARCHAR(255) DEFAULT NULL');
        $this->addSql('CREATE TEMPORARY TABLE __temp__settings AS SELECT id, email_account_username, email_account_password, email_account_host, email_account_port, current_theme, current_layout_data FROM settings');
        $this->addSql('DROP TABLE settings');
        $this->addSql('CREATE TABLE settings (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, email_account_username VARCHAR(255) DEFAULT NULL, email_account_password VARCHAR(255) DEFAULT NULL, email_account_host VARCHAR(255) DEFAULT NULL, email_account_port VARCHAR(255) DEFAULT NULL, current_theme VARCHAR(255) NOT NULL, layout_data CLOB DEFAULT NULL --(DC2Type:json)
        )');
        $this->addSql('INSERT INTO settings (id, email_account_username, email_account_password, email_account_host, email_account_port, current_theme, layout_data) SELECT id, email_account_username, email_account_password, email_account_host, email_account_port, current_theme, current_layout_data FROM __temp__settings');
        $this->addSql('DROP TABLE __temp__settings');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__page AS SELECT id, path, type, data, meta FROM page');
        $this->addSql('DROP TABLE page');
        $this->addSql('CREATE TABLE page (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, path VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, data CLOB NOT NULL --(DC2Type:json)
        , meta CLOB NOT NULL --(DC2Type:json)
        )');
        $this->addSql('INSERT INTO page (id, path, type, data, meta) SELECT id, path, type, data, meta FROM __temp__page');
        $this->addSql('DROP TABLE __temp__page');
        $this->addSql('CREATE TEMPORARY TABLE __temp__settings AS SELECT id, email_account_username, email_account_password, email_account_host, email_account_port, current_theme, layout_data FROM settings');
        $this->addSql('DROP TABLE settings');
        $this->addSql('CREATE TABLE settings (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, email_account_username VARCHAR(255) DEFAULT NULL, email_account_password VARCHAR(255) DEFAULT NULL, email_account_host VARCHAR(255) DEFAULT NULL, email_account_port VARCHAR(255) DEFAULT NULL, current_theme VARCHAR(255) NOT NULL, current_layout_data CLOB DEFAULT NULL --(DC2Type:json)
        , current_layout_name VARCHAR(255) DEFAULT NULL)');
        $this->addSql('INSERT INTO settings (id, email_account_username, email_account_password, email_account_host, email_account_port, current_theme, current_layout_data) SELECT id, email_account_username, email_account_password, email_account_host, email_account_port, current_theme, layout_data FROM __temp__settings');
        $this->addSql('DROP TABLE __temp__settings');
    }
}
