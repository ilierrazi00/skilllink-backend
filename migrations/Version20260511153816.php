<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260511153816 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE company (id INT AUTO_INCREMENT NOT NULL, nom_entreprise VARCHAR(150) NOT NULL, description LONGTEXT DEFAULT NULL, secteur VARCHAR(100) DEFAULT NULL, taille VARCHAR(50) DEFAULT NULL, localisation VARCHAR(150) NOT NULL, site_web VARCHAR(255) DEFAULT NULL, user_id INT NOT NULL, UNIQUE INDEX UNIQ_4FBF094FA76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE company ADD CONSTRAINT FK_4FBF094FA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE candidate_profile ADD CONSTRAINT FK_E8607AEA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE skill_candidate_profile ADD CONSTRAINT FK_CBBC6FB65585C142 FOREIGN KEY (skill_id) REFERENCES skill (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE skill_candidate_profile ADD CONSTRAINT FK_CBBC6FB6FE3D0586 FOREIGN KEY (candidate_profile_id) REFERENCES candidate_profile (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE company DROP FOREIGN KEY FK_4FBF094FA76ED395');
        $this->addSql('DROP TABLE company');
        $this->addSql('ALTER TABLE candidate_profile DROP FOREIGN KEY FK_E8607AEA76ED395');
        $this->addSql('ALTER TABLE skill_candidate_profile DROP FOREIGN KEY FK_CBBC6FB65585C142');
        $this->addSql('ALTER TABLE skill_candidate_profile DROP FOREIGN KEY FK_CBBC6FB6FE3D0586');
    }
}
