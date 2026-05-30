<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260511213356 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE application ADD CONSTRAINT FK_A45BDDC1FE3D0586 FOREIGN KEY (candidate_profile_id) REFERENCES candidate_profile (id)');
        $this->addSql('ALTER TABLE application ADD CONSTRAINT FK_A45BDDC13481D195 FOREIGN KEY (job_offer_id) REFERENCES job_offer (id)');
        $this->addSql('ALTER TABLE candidate_profile ADD CONSTRAINT FK_E8607AEA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE company ADD CONSTRAINT FK_4FBF094FA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE job_offer ADD CONSTRAINT FK_288A3A4E979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)');
        $this->addSql('ALTER TABLE job_offer_skill ADD CONSTRAINT FK_BE7C82D73481D195 FOREIGN KEY (job_offer_id) REFERENCES job_offer (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE job_offer_skill ADD CONSTRAINT FK_BE7C82D75585C142 FOREIGN KEY (skill_id) REFERENCES skill (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE skill_candidate_profile ADD CONSTRAINT FK_CBBC6FB65585C142 FOREIGN KEY (skill_id) REFERENCES skill (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE skill_candidate_profile ADD CONSTRAINT FK_CBBC6FB6FE3D0586 FOREIGN KEY (candidate_profile_id) REFERENCES candidate_profile (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE application DROP FOREIGN KEY FK_A45BDDC1FE3D0586');
        $this->addSql('ALTER TABLE application DROP FOREIGN KEY FK_A45BDDC13481D195');
        $this->addSql('ALTER TABLE candidate_profile DROP FOREIGN KEY FK_E8607AEA76ED395');
        $this->addSql('ALTER TABLE company DROP FOREIGN KEY FK_4FBF094FA76ED395');
        $this->addSql('ALTER TABLE job_offer DROP FOREIGN KEY FK_288A3A4E979B1AD6');
        $this->addSql('ALTER TABLE job_offer_skill DROP FOREIGN KEY FK_BE7C82D73481D195');
        $this->addSql('ALTER TABLE job_offer_skill DROP FOREIGN KEY FK_BE7C82D75585C142');
        $this->addSql('ALTER TABLE skill_candidate_profile DROP FOREIGN KEY FK_CBBC6FB65585C142');
        $this->addSql('ALTER TABLE skill_candidate_profile DROP FOREIGN KEY FK_CBBC6FB6FE3D0586');
    }
}
