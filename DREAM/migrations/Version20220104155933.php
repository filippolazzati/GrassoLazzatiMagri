<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220104155933 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE farm DROP CONSTRAINT fk_5816d04511bd6139');
        $this->addSql('DROP INDEX uniq_5816d04511bd6139');
        $this->addSql('ALTER TABLE farm RENAME COLUMN has_id TO farmer_id');
        $this->addSql('ALTER TABLE farm ADD CONSTRAINT FK_5816D04513481D2B FOREIGN KEY (farmer_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5816D04513481D2B ON farm (farmer_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE farm DROP CONSTRAINT FK_5816D04513481D2B');
        $this->addSql('DROP INDEX UNIQ_5816D04513481D2B');
        $this->addSql('ALTER TABLE farm RENAME COLUMN farmer_id TO has_id');
        $this->addSql('ALTER TABLE farm ADD CONSTRAINT fk_5816d04511bd6139 FOREIGN KEY (has_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX uniq_5816d04511bd6139 ON farm (has_id)');
    }
}
