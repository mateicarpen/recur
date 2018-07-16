<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20180626162446 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE task (id INT AUTO_INCREMENT NOT NULL, frequency_unit_id INT NOT NULL, name VARCHAR(255) NOT NULL, frequency INT NOT NULL, start_date DATE NOT NULL, adjust_on_completion TINYINT(1) NOT NULL, create_date DATETIME NOT NULL, update_date DATETIME NOT NULL, INDEX IDX_527EDB25D9AEC3CF (frequency_unit_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE frequency_unit (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE task ADD CONSTRAINT FK_527EDB25D9AEC3CF FOREIGN KEY (frequency_unit_id) REFERENCES frequency_unit (id) ON DELETE CASCADE');

        $this->addSql('INSERT INTO frequency_unit (id, name) VALUES (1, "day(s)"), (2, "week(s)"), (3, "month(s)")');
    }

    public function down(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE task DROP FOREIGN KEY FK_527EDB25D9AEC3CF');
        $this->addSql('DROP TABLE task');
        $this->addSql('DROP TABLE frequency_unit');
    }
}
