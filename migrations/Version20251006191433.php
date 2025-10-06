<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251006191433 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__settings AS SELECT id FROM settings');
        $this->addSql('DROP TABLE settings');
        $this->addSql('CREATE TABLE settings (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, email_account_username VARCHAR(255) DEFAULT NULL, email_account_password VARCHAR(255) DEFAULT NULL, email_account_host VARCHAR(255) DEFAULT NULL, email_account_port INTEGER DEFAULT NULL)');
        $this->addSql('INSERT INTO settings (id) SELECT id FROM __temp__settings');
        $this->addSql('DROP TABLE __temp__settings');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__settings AS SELECT id FROM settings');
        $this->addSql('DROP TABLE settings');
        $this->addSql('CREATE TABLE settings (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, email_account CLOB NOT NULL --(DC2Type:json)
        )');
        $this->addSql('INSERT INTO settings (id) SELECT id FROM __temp__settings');
        $this->addSql('DROP TABLE __temp__settings');
    }
}
