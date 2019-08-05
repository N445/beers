<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190805192858 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE brewer (id INT AUTO_INCREMENT NOT NULL, coordinate_id INT DEFAULT NULL, name VARCHAR(150) NOT NULL, address VARCHAR(150) DEFAULT NULL, city VARCHAR(100) DEFAULT NULL, state VARCHAR(100) DEFAULT NULL, country VARCHAR(100) DEFAULT NULL, website VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_8C2B4A4B98BBE953 (coordinate_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE category (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(150) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE style (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(150) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE beer (id INT AUTO_INCREMENT NOT NULL, style_id INT DEFAULT NULL, category_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, alcohol DOUBLE PRECISION NOT NULL, description LONGTEXT DEFAULT NULL, last_mod DATETIME NOT NULL, INDEX IDX_58F666ADBACD6074 (style_id), INDEX IDX_58F666AD12469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE coordinate (id INT AUTO_INCREMENT NOT NULL, lat DOUBLE PRECISION NOT NULL, lng DOUBLE PRECISION NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE brewer ADD CONSTRAINT FK_8C2B4A4B98BBE953 FOREIGN KEY (coordinate_id) REFERENCES coordinate (id)');
        $this->addSql('ALTER TABLE beer ADD CONSTRAINT FK_58F666ADBACD6074 FOREIGN KEY (style_id) REFERENCES style (id)');
        $this->addSql('ALTER TABLE beer ADD CONSTRAINT FK_58F666AD12469DE2 FOREIGN KEY (category_id) REFERENCES category (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE beer DROP FOREIGN KEY FK_58F666AD12469DE2');
        $this->addSql('ALTER TABLE beer DROP FOREIGN KEY FK_58F666ADBACD6074');
        $this->addSql('ALTER TABLE brewer DROP FOREIGN KEY FK_8C2B4A4B98BBE953');
        $this->addSql('DROP TABLE brewer');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE style');
        $this->addSql('DROP TABLE beer');
        $this->addSql('DROP TABLE coordinate');
    }
}
