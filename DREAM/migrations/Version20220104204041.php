<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220104204041 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE production_data ADD farmer_id INT NOT NULL');
        $this->addSql('ALTER TABLE production_data ADD CONSTRAINT FK_76E3B87713481D2B FOREIGN KEY (farmer_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_76E3B87713481D2B ON production_data (farmer_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE production_data DROP CONSTRAINT FK_76E3B87713481D2B');
        $this->addSql('DROP INDEX IDX_76E3B87713481D2B');
        $this->addSql('ALTER TABLE production_data DROP farmer_id');
    }
}
