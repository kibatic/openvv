<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221120060952 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE project_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE project (id INT NOT NULL, owner_id INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_2FB3D0EE7E3C61F9 ON project (owner_id)');
        $this->addSql('COMMENT ON COLUMN project.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE project ADD CONSTRAINT FK_2FB3D0EE7E3C61F9 FOREIGN KEY (owner_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE project_id_seq CASCADE');
        $this->addSql('ALTER TABLE project DROP CONSTRAINT FK_2FB3D0EE7E3C61F9');
        $this->addSql('DROP TABLE project');
    }
}
