<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190408162602 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE page (id INT AUTO_INCREMENT NOT NULL, image_id INT DEFAULT NULL, status VARCHAR(20) NOT NULL, code VARCHAR(255) DEFAULT NULL, title VARCHAR(255) DEFAULT NULL, name VARCHAR(255) DEFAULT NULL, slug VARCHAR(255) NOT NULL, excerpt LONGTEXT DEFAULT NULL, description LONGTEXT DEFAULT NULL, meta_title VARCHAR(255) DEFAULT NULL, meta_description VARCHAR(255) DEFAULT NULL, meta_keywords VARCHAR(255) DEFAULT NULL, is_page_title_enabled TINYINT(1) NOT NULL, is_page_excerpt_enabled TINYINT(1) NOT NULL, is_page_description_enabled TINYINT(1) NOT NULL, is_page_image_enabled TINYINT(1) NOT NULL, is_page_gallery_enabled TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, publish_at DATETIME DEFAULT NULL, publish_until DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_140AB6203DA5256D (image_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE page_gallery (id INT AUTO_INCREMENT NOT NULL, page_id INT DEFAULT NULL, INDEX IDX_BD4B93AFC4663E4 (page_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE page_gallery_crop (id INT AUTO_INCREMENT NOT NULL, image_id INT DEFAULT NULL, filter VARCHAR(255) NOT NULL, crop VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_C0C1B9593DA5256D (image_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE page_image (id INT AUTO_INCREMENT NOT NULL, image_id INT DEFAULT NULL, title VARCHAR(255) DEFAULT NULL, alt VARCHAR(255) DEFAULT NULL, class VARCHAR(255) DEFAULT NULL, position INT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, INDEX IDX_A3BCFB893DA5256D (image_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE page_image_crop (id INT AUTO_INCREMENT NOT NULL, image_id INT DEFAULT NULL, filter VARCHAR(255) NOT NULL, crop VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_1418E69B3DA5256D (image_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE page ADD CONSTRAINT FK_140AB6203DA5256D FOREIGN KEY (image_id) REFERENCES page_image (id)');
        $this->addSql('ALTER TABLE page_gallery ADD CONSTRAINT FK_BD4B93AFC4663E4 FOREIGN KEY (page_id) REFERENCES page (id)');
        $this->addSql('ALTER TABLE page_gallery_crop ADD CONSTRAINT FK_C0C1B9593DA5256D FOREIGN KEY (image_id) REFERENCES page_gallery (id)');
        $this->addSql('ALTER TABLE page_image ADD CONSTRAINT FK_A3BCFB893DA5256D FOREIGN KEY (image_id) REFERENCES image (id)');
        $this->addSql('ALTER TABLE page_image_crop ADD CONSTRAINT FK_1418E69B3DA5256D FOREIGN KEY (image_id) REFERENCES page_image (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE page_gallery DROP FOREIGN KEY FK_BD4B93AFC4663E4');
        $this->addSql('ALTER TABLE page_gallery_crop DROP FOREIGN KEY FK_C0C1B9593DA5256D');
        $this->addSql('ALTER TABLE page DROP FOREIGN KEY FK_140AB6203DA5256D');
        $this->addSql('ALTER TABLE page_image_crop DROP FOREIGN KEY FK_1418E69B3DA5256D');
        $this->addSql('DROP TABLE page');
        $this->addSql('DROP TABLE page_gallery');
        $this->addSql('DROP TABLE page_gallery_crop');
        $this->addSql('DROP TABLE page_image');
        $this->addSql('DROP TABLE page_image_crop');
    }
}
