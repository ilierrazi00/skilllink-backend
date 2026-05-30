<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260511142655 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE candidate_profile (id INT AUTO_INCREMENT NOT NULL, titre_profil VARCHAR(150) NOT NULL, bio LONGTEXT DEFAULT NULL, experience_annees INT NOT NULL, cv_url VARCHAR(255) DEFAULT NULL, localisation VARCHAR(150) NOT NULL, disponibilite TINYINT NOT NULL, user_id INT NOT NULL, UNIQUE INDEX UNIQ_E8607AEA76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE candidate_profile ADD CONSTRAINT FK_E8607AEA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE candidate_profile DROP FOREIGN KEY FK_E8607AEA76ED395');
        $this->addSql('DROP TABLE candidate_profile');
    }
}
