<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260511150333 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE candidate_profile ADD CONSTRAINT FK_E8607AEA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE skill_candidate_profile ADD CONSTRAINT FK_CBBC6FB65585C142 FOREIGN KEY (skill_id) REFERENCES skill (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE skill_candidate_profile ADD CONSTRAINT FK_CBBC6FB6FE3D0586 FOREIGN KEY (candidate_profile_id) REFERENCES candidate_profile (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE candidate_profile DROP FOREIGN KEY FK_E8607AEA76ED395');
        $this->addSql('ALTER TABLE skill_candidate_profile DROP FOREIGN KEY FK_CBBC6FB65585C142');
        $this->addSql('ALTER TABLE skill_candidate_profile DROP FOREIGN KEY FK_CBBC6FB6FE3D0586');
    }
}
