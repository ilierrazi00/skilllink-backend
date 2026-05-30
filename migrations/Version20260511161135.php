<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260511161135 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE job_offer (id INT AUTO_INCREMENT NOT NULL, titre VARCHAR(150) NOT NULL, description LONGTEXT DEFAULT NULL, type_contrat VARCHAR(50) NOT NULL, salaire INT DEFAULT NULL, remote_possible TINYINT NOT NULL, localisation VARCHAR(150) NOT NULL, date_publication DATETIME NOT NULL, company_id INT NOT NULL, INDEX IDX_288A3A4E979B1AD6 (company_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE job_offer_skill (job_offer_id INT NOT NULL, skill_id INT NOT NULL, INDEX IDX_BE7C82D73481D195 (job_offer_id), INDEX IDX_BE7C82D75585C142 (skill_id), PRIMARY KEY (job_offer_id, skill_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE job_offer ADD CONSTRAINT FK_288A3A4E979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)');
        $this->addSql('ALTER TABLE job_offer_skill ADD CONSTRAINT FK_BE7C82D73481D195 FOREIGN KEY (job_offer_id) REFERENCES job_offer (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE job_offer_skill ADD CONSTRAINT FK_BE7C82D75585C142 FOREIGN KEY (skill_id) REFERENCES skill (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE candidate_profile ADD CONSTRAINT FK_E8607AEA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE company ADD CONSTRAINT FK_4FBF094FA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE skill_candidate_profile ADD CONSTRAINT FK_CBBC6FB65585C142 FOREIGN KEY (skill_id) REFERENCES skill (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE skill_candidate_profile ADD CONSTRAINT FK_CBBC6FB6FE3D0586 FOREIGN KEY (candidate_profile_id) REFERENCES candidate_profile (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE job_offer DROP FOREIGN KEY FK_288A3A4E979B1AD6');
        $this->addSql('ALTER TABLE job_offer_skill DROP FOREIGN KEY FK_BE7C82D73481D195');
        $this->addSql('ALTER TABLE job_offer_skill DROP FOREIGN KEY FK_BE7C82D75585C142');
        $this->addSql('DROP TABLE job_offer');
        $this->addSql('DROP TABLE job_offer_skill');
        $this->addSql('ALTER TABLE candidate_profile DROP FOREIGN KEY FK_E8607AEA76ED395');
        $this->addSql('ALTER TABLE company DROP FOREIGN KEY FK_4FBF094FA76ED395');
        $this->addSql('ALTER TABLE skill_candidate_profile DROP FOREIGN KEY FK_CBBC6FB65585C142');
        $this->addSql('ALTER TABLE skill_candidate_profile DROP FOREIGN KEY FK_CBBC6FB6FE3D0586');
    }
}
