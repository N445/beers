<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190805210920 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE UNIQUE INDEX UNIQ_8C2B4A4B5E237E06 ON brewer (name)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_64C19C15E237E06 ON category (name)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_33BDB86A5E237E06 ON style (name)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_58F666AD5E237E06 ON beer (name)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX UNIQ_58F666AD5E237E06 ON beer');
        $this->addSql('DROP INDEX UNIQ_8C2B4A4B5E237E06 ON brewer');
        $this->addSql('DROP INDEX UNIQ_64C19C15E237E06 ON category');
        $this->addSql('DROP INDEX UNIQ_33BDB86A5E237E06 ON style');
    }
}
