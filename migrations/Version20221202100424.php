<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221202100424 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE media ADD order_in_project INT DEFAULT NULL');
        $this->addSql('ALTER TABLE media ADD initial_latitude DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE media ADD initial_longitude DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE project ALTER renderer TYPE VARCHAR(50)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE project ALTER renderer TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE media DROP order_in_project');
        $this->addSql('ALTER TABLE media DROP initial_latitude');
        $this->addSql('ALTER TABLE media DROP initial_longitude');
    }
}
