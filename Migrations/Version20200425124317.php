<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200425124317 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE aropixel_block (id INT AUTO_INCREMENT NOT NULL, page_id INT DEFAULT NULL, code VARCHAR(255) DEFAULT NULL, INDEX IDX_B45063F9C4663E4 (page_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE aropixel_block ADD CONSTRAINT FK_B45063F9C4663E4 FOREIGN KEY (page_id) REFERENCES aropixel_page (id)');
        $this->addSql('CREATE TABLE aropixel_block_input (id INT AUTO_INCREMENT NOT NULL, block_id INT DEFAULT NULL, code VARCHAR(255) DEFAULT NULL, type VARCHAR(255) DEFAULT NULL, INDEX IDX_B745C269E9ED820C (block_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE aropixel_block_input ADD CONSTRAINT FK_B745C269E9ED820C FOREIGN KEY (block_id) REFERENCES aropixel_block (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE aropixel_block');
        $this->addSql('DROP TABLE aropixel_block_input');

    }
}
