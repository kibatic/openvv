<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221130160626 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE link_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE link (id INT NOT NULL, source_media_id INT NOT NULL, target_media_id INT NOT NULL, source_texture_x INT DEFAULT NULL, source_texture_y INT DEFAULT NULL, source_latitude DOUBLE PRECISION DEFAULT NULL, source_longitude DOUBLE PRECISION DEFAULT NULL, target_latitude DOUBLE PRECISION DEFAULT NULL, target_longitude DOUBLE PRECISION DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_36AC99F1A9BFEBA7 ON link (source_media_id)');
        $this->addSql('CREATE INDEX IDX_36AC99F130340D1C ON link (target_media_id)');
        $this->addSql('ALTER TABLE link ADD CONSTRAINT FK_36AC99F1A9BFEBA7 FOREIGN KEY (source_media_id) REFERENCES media (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE link ADD CONSTRAINT FK_36AC99F130340D1C FOREIGN KEY (target_media_id) REFERENCES media (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE media ALTER name DROP DEFAULT');
        $this->addSql('ALTER TABLE project ADD renderer VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE link_id_seq CASCADE');
        $this->addSql('ALTER TABLE link DROP CONSTRAINT FK_36AC99F1A9BFEBA7');
        $this->addSql('ALTER TABLE link DROP CONSTRAINT FK_36AC99F130340D1C');
        $this->addSql('DROP TABLE link');
        $this->addSql('ALTER TABLE media ALTER name SET DEFAULT \'media\'');
        $this->addSql('ALTER TABLE project DROP renderer');
    }
}
