<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230420210732 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE content_panier DROP INDEX UNIQ_4CFA7D5EF77D927C, ADD INDEX IDX_4CFA7D5EF77D927C (panier_id)');
        $this->addSql('ALTER TABLE content_panier CHANGE panier_id panier_id INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE content_panier DROP INDEX IDX_4CFA7D5EF77D927C, ADD UNIQUE INDEX UNIQ_4CFA7D5EF77D927C (panier_id)');
        $this->addSql('ALTER TABLE content_panier CHANGE panier_id panier_id INT NOT NULL');
    }
}
