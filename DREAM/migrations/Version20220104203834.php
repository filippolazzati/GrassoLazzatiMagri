<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220104203834 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE production_data_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE production_data (id INT NOT NULL, seeding_date DATE NOT NULL, seeded_area INT NOT NULL, product VARCHAR(50) NOT NULL, fertilizer VARCHAR(50) NOT NULL, harvest_volume DOUBLE PRECISION NOT NULL, watering BOOLEAN NOT NULL, report_date DATE NOT NULL, comment VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE production_data_id_seq CASCADE');
        $this->addSql('DROP TABLE production_data');
    }
}
