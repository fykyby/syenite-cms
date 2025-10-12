<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251012223548 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE layout_data (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, data CLOB DEFAULT NULL --(DC2Type:json)
        )');
        $this->addSql('CREATE TEMPORARY TABLE __temp__settings AS SELECT id, email_account_username, email_account_password, email_account_host, email_account_port, current_theme FROM settings');
        $this->addSql('DROP TABLE settings');
        $this->addSql('CREATE TABLE settings (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, email_account_username VARCHAR(255) DEFAULT NULL, email_account_password VARCHAR(255) DEFAULT NULL, email_account_host VARCHAR(255) DEFAULT NULL, email_account_port VARCHAR(255) DEFAULT NULL, current_theme VARCHAR(255) NOT NULL)');
        $this->addSql('INSERT INTO settings (id, email_account_username, email_account_password, email_account_host, email_account_port, current_theme) SELECT id, email_account_username, email_account_password, email_account_host, email_account_port, current_theme FROM __temp__settings');
        $this->addSql('DROP TABLE __temp__settings');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE layout_data');
        $this->addSql('ALTER TABLE settings ADD COLUMN layout_data CLOB DEFAULT NULL');
    }
}
