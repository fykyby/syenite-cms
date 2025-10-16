<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251016211018 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE settings');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE settings (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, email_account_username VARCHAR(255) DEFAULT NULL COLLATE "BINARY", email_account_password VARCHAR(255) DEFAULT NULL COLLATE "BINARY", email_account_host VARCHAR(255) DEFAULT NULL COLLATE "BINARY", email_account_port VARCHAR(255) DEFAULT NULL COLLATE "BINARY", current_theme VARCHAR(255) NOT NULL COLLATE "BINARY")');
    }
}
