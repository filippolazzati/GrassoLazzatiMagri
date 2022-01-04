<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220104210012 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE weather_forecast_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE weather_report_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE weather_forecast (id INT NOT NULL, date DATE NOT NULL, city VARCHAR(50) NOT NULL, weather VARCHAR(50) NOT NULL, t_max INT NOT NULL, t_min INT NOT NULL, t_avg INT NOT NULL, rain_mm INT NOT NULL, wind_speed DOUBLE PRECISION NOT NULL, wind_direction VARCHAR(3) NOT NULL, humidity INT NOT NULL, pressure INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE weather_report (id INT NOT NULL, date DATE NOT NULL, city VARCHAR(50) NOT NULL, weather VARCHAR(50) NOT NULL, t_max INT NOT NULL, t_min INT NOT NULL, t_avg INT NOT NULL, rain_mm INT NOT NULL, wind_speed DOUBLE PRECISION NOT NULL, wind_direction VARCHAR(10) NOT NULL, humidity INT NOT NULL, pressure INT NOT NULL, PRIMARY KEY(id))');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE weather_forecast_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE weather_report_id_seq CASCADE');
        $this->addSql('DROP TABLE weather_forecast');
        $this->addSql('DROP TABLE weather_report');
    }
}
