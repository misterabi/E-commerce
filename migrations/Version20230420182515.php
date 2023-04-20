<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230420182515 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE content_panier (id INT AUTO_INCREMENT NOT NULL, panier_id INT NOT NULL, quantite INT NOT NULL, date DATETIME NOT NULL, UNIQUE INDEX UNIQ_4CFA7D5EF77D927C (panier_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE content_panier_produit (content_panier_id INT NOT NULL, produit_id INT NOT NULL, INDEX IDX_B9F4D272C79ADA7F (content_panier_id), INDEX IDX_B9F4D272F347EFB (produit_id), PRIMARY KEY(content_panier_id, produit_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE content_panier ADD CONSTRAINT FK_4CFA7D5EF77D927C FOREIGN KEY (panier_id) REFERENCES panier (id)');
        $this->addSql('ALTER TABLE content_panier_produit ADD CONSTRAINT FK_B9F4D272C79ADA7F FOREIGN KEY (content_panier_id) REFERENCES content_panier (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE content_panier_produit ADD CONSTRAINT FK_B9F4D272F347EFB FOREIGN KEY (produit_id) REFERENCES produit (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE contenue_panier DROP FOREIGN KEY FK_1816834F77D927C');
        $this->addSql('ALTER TABLE contenue_panier_produit DROP FOREIGN KEY FK_CBFBC9272B8DA64');
        $this->addSql('ALTER TABLE contenue_panier_produit DROP FOREIGN KEY FK_CBFBC92F347EFB');
        $this->addSql('DROP TABLE contenue_panier');
        $this->addSql('DROP TABLE contenue_panier_produit');
        $this->addSql('ALTER TABLE panier ADD utilisateur_id INT NOT NULL');
        $this->addSql('ALTER TABLE panier ADD CONSTRAINT FK_24CC0DF2FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_24CC0DF2FB88E14F ON panier (utilisateur_id)');
        $this->addSql('ALTER TABLE utilisateur DROP FOREIGN KEY FK_1D1C63B3F77D927C');
        $this->addSql('DROP INDEX IDX_1D1C63B3F77D927C ON utilisateur');
        $this->addSql('ALTER TABLE utilisateur DROP panier_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE contenue_panier (id INT AUTO_INCREMENT NOT NULL, panier_id INT DEFAULT NULL, quantit?e INT NOT NULL, date DATETIME NOT NULL, INDEX IDX_1816834F77D927C (panier_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE contenue_panier_produit (contenue_panier_id INT NOT NULL, produit_id INT NOT NULL, INDEX IDX_CBFBC9272B8DA64 (contenue_panier_id), INDEX IDX_CBFBC92F347EFB (produit_id), PRIMARY KEY(contenue_panier_id, produit_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE contenue_panier ADD CONSTRAINT FK_1816834F77D927C FOREIGN KEY (panier_id) REFERENCES panier (id)');
        $this->addSql('ALTER TABLE contenue_panier_produit ADD CONSTRAINT FK_CBFBC9272B8DA64 FOREIGN KEY (contenue_panier_id) REFERENCES contenue_panier (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE contenue_panier_produit ADD CONSTRAINT FK_CBFBC92F347EFB FOREIGN KEY (produit_id) REFERENCES produit (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE content_panier DROP FOREIGN KEY FK_4CFA7D5EF77D927C');
        $this->addSql('ALTER TABLE content_panier_produit DROP FOREIGN KEY FK_B9F4D272C79ADA7F');
        $this->addSql('ALTER TABLE content_panier_produit DROP FOREIGN KEY FK_B9F4D272F347EFB');
        $this->addSql('DROP TABLE content_panier');
        $this->addSql('DROP TABLE content_panier_produit');
        $this->addSql('ALTER TABLE panier DROP FOREIGN KEY FK_24CC0DF2FB88E14F');
        $this->addSql('DROP INDEX UNIQ_24CC0DF2FB88E14F ON panier');
        $this->addSql('ALTER TABLE panier DROP utilisateur_id');
        $this->addSql('ALTER TABLE utilisateur ADD panier_id INT NOT NULL');
        $this->addSql('ALTER TABLE utilisateur ADD CONSTRAINT FK_1D1C63B3F77D927C FOREIGN KEY (panier_id) REFERENCES panier (id)');
        $this->addSql('CREATE INDEX IDX_1D1C63B3F77D927C ON utilisateur (panier_id)');
    }
}
