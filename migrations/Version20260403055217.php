<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260403055217 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE post DROP FOREIGN KEY `FK_5A8A6C8DED766068`');
        $this->addSql('DROP INDEX IDX_5A8A6C8DED766068 ON post');
        $this->addSql('ALTER TABLE post CHANGE username_id my_user_id INT NOT NULL');
        $this->addSql('ALTER TABLE post ADD CONSTRAINT FK_5A8A6C8D2D977FB9 FOREIGN KEY (my_user_id) REFERENCES `user` (id)');
        $this->addSql('CREATE INDEX IDX_5A8A6C8D2D977FB9 ON post (my_user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE post DROP FOREIGN KEY FK_5A8A6C8D2D977FB9');
        $this->addSql('DROP INDEX IDX_5A8A6C8D2D977FB9 ON post');
        $this->addSql('ALTER TABLE post CHANGE my_user_id username_id INT NOT NULL');
        $this->addSql('ALTER TABLE post ADD CONSTRAINT `FK_5A8A6C8DED766068` FOREIGN KEY (username_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_5A8A6C8DED766068 ON post (username_id)');
    }
}
