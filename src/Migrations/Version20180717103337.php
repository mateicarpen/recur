<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20180717103337 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE task_log DROP FOREIGN KEY FK_E0BD90428DB60186');
        $this->addSql('ALTER TABLE task_log ADD CONSTRAINT FK_E0BD90428DB60186 FOREIGN KEY (task_id) REFERENCES task (id) ON DELETE CASCADE');

        $this->addSql('DELETE FROM task');

        $this->addSql('ALTER TABLE task ADD user_id INT NOT NULL AFTER `id`');
        $this->addSql('ALTER TABLE task ADD CONSTRAINT FK_527EDB25A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_527EDB25A76ED395 ON task (user_id)');
    }

    public function down(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE task DROP FOREIGN KEY FK_527EDB25A76ED395');
        $this->addSql('DROP INDEX IDX_527EDB25A76ED395 ON task');
        $this->addSql('ALTER TABLE task DROP user_id');

        $this->addSql('ALTER TABLE task_log DROP FOREIGN KEY FK_E0BD90428DB60186');
        $this->addSql('ALTER TABLE task_log ADD CONSTRAINT FK_E0BD90428DB60186 FOREIGN KEY (task_id) REFERENCES task (id)');
    }
}
