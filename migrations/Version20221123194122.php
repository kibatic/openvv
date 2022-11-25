<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221123194122 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE share_id_seq CASCADE');
        $this->addSql('ALTER TABLE share DROP CONSTRAINT fk_ef069d5a166d1f9c');
        $this->addSql('DROP TABLE share');
        $this->addSql('ALTER TABLE project ADD share_duration_in_days INT DEFAULT NULL');
        $this->addSql('ALTER TABLE project ADD share_uid VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE project ADD share_started_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN project.share_started_at IS \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('CREATE SEQUENCE share_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE share (id INT NOT NULL, project_id INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, duration_in_days INT DEFAULT NULL, uid VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_ef069d5a166d1f9c ON share (project_id)');
        $this->addSql('COMMENT ON COLUMN share.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE share ADD CONSTRAINT fk_ef069d5a166d1f9c FOREIGN KEY (project_id) REFERENCES project (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE project DROP share_duration_in_days');
        $this->addSql('ALTER TABLE project DROP share_uid');
        $this->addSql('ALTER TABLE project DROP share_started_at');
    }
}
