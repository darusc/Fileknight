<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250905161213 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE directory CHANGE owner_id owner_id VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE refresh_token CHANGE user_id user_id VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE user CHANGE id id VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE directory CHANGE owner_id owner_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user CHANGE id id INT AUTO_INCREMENT NOT NULL');
        $this->addSql('ALTER TABLE refresh_token CHANGE user_id user_id INT NOT NULL');
    }
}
