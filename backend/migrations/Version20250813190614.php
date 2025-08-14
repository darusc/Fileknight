<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250813190614 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE directory (id INT AUTO_INCREMENT NOT NULL, parent_id INT DEFAULT NULL, owner_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, INDEX IDX_467844DA727ACA70 (parent_id), INDEX IDX_467844DA7E3C61F9 (owner_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE file (id INT AUTO_INCREMENT NOT NULL, directory_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, type VARCHAR(15) NOT NULL, size INT NOT NULL, INDEX IDX_8C9F36102C94069F (directory_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE directory ADD CONSTRAINT FK_467844DA727ACA70 FOREIGN KEY (parent_id) REFERENCES directory (id)');
        $this->addSql('ALTER TABLE directory ADD CONSTRAINT FK_467844DA7E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE file ADD CONSTRAINT FK_8C9F36102C94069F FOREIGN KEY (directory_id) REFERENCES directory (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE directory DROP FOREIGN KEY FK_467844DA727ACA70');
        $this->addSql('ALTER TABLE directory DROP FOREIGN KEY FK_467844DA7E3C61F9');
        $this->addSql('ALTER TABLE file DROP FOREIGN KEY FK_8C9F36102C94069F');
        $this->addSql('DROP TABLE directory');
        $this->addSql('DROP TABLE file');
    }
}
