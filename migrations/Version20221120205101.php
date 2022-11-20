<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221120205101 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE share_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE share (id INT NOT NULL, project_id INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, duration_in_days INT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_EF069D5A166D1F9C ON share (project_id)');
        $this->addSql('COMMENT ON COLUMN share.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE share ADD CONSTRAINT FK_EF069D5A166D1F9C FOREIGN KEY (project_id) REFERENCES project (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE share_id_seq CASCADE');
        $this->addSql('ALTER TABLE share DROP CONSTRAINT FK_EF069D5A166D1F9C');
        $this->addSql('DROP TABLE share');
    }
}
