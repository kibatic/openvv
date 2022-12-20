<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221220171700 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE link RENAME COLUMN source_latitude TO source_pitch');
        $this->addSql('ALTER TABLE link RENAME COLUMN source_longitude TO source_yaw');
        $this->addSql('ALTER TABLE link RENAME COLUMN target_latitude TO target_pitch');
        $this->addSql('ALTER TABLE link RENAME COLUMN target_longitude TO target_yaw');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE link RENAME COLUMN source_pitch TO source_latitude');
        $this->addSql('ALTER TABLE link RENAME COLUMN source_yaw TO source_longitude');
        $this->addSql('ALTER TABLE link RENAME COLUMN target_pitch TO target_latitude');
        $this->addSql('ALTER TABLE link RENAME COLUMN target_yaw TO target_longitude');
    }
}
