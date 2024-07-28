<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240728081013 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE machine ADD process_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE machine ADD CONSTRAINT FK_1505DF847EC2F574 FOREIGN KEY (process_id) REFERENCES process (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1505DF847EC2F574 ON machine (process_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE machine DROP FOREIGN KEY FK_1505DF847EC2F574');
        $this->addSql('DROP INDEX UNIQ_1505DF847EC2F574 ON machine');
        $this->addSql('ALTER TABLE machine DROP process_id');
    }
}
