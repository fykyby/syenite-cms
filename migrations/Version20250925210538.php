<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250925210538 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE media (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, type VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, variants CLOB NOT NULL --(DC2Type:json)
        )');
        $this->addSql('CREATE TEMPORARY TABLE __temp__page AS SELECT id, path, type, data, meta FROM page');
        $this->addSql('DROP TABLE page');
        $this->addSql('CREATE TABLE page (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, path VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, data CLOB NOT NULL --(DC2Type:json)
        , meta CLOB NOT NULL --(DC2Type:json)
        )');
        $this->addSql('INSERT INTO page (id, path, type, data, meta) SELECT id, path, type, data, meta FROM __temp__page');
        $this->addSql('DROP TABLE __temp__page');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE media');
        $this->addSql('CREATE TEMPORARY TABLE __temp__page AS SELECT id, path, type, data, meta FROM page');
        $this->addSql('DROP TABLE page');
        $this->addSql('CREATE TABLE page (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, path VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, data CLOB NOT NULL --(DC2Type:json)
        , meta CLOB NOT NULL)');
        $this->addSql('INSERT INTO page (id, path, type, data, meta) SELECT id, path, type, data, meta FROM __temp__page');
        $this->addSql('DROP TABLE __temp__page');
    }
}
