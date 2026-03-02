<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260302220402 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE quiz_questions (id INT AUTO_INCREMENT NOT NULL, level_id INT NOT NULL, topic_id INT NOT NULL, text VARCHAR(255) NOT NULL, is_active TINYINT(1) NOT NULL, INDEX IDX_8CBC25335FB14BA7 (level_id), INDEX IDX_8CBC25331F55203D (topic_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('INSERT INTO quiz_questions (id, level_id, topic_id, text, is_active) SELECT id, level_id, topic_id, text, is_active FROM questions');
        $this->addSql('ALTER TABLE quiz_questions ADD CONSTRAINT FK_8CBC25335FB14BA7 FOREIGN KEY (level_id) REFERENCES level (id)');
        $this->addSql('ALTER TABLE quiz_questions ADD CONSTRAINT FK_8CBC25331F55203D FOREIGN KEY (topic_id) REFERENCES topic (id)');
        $this->addSql('ALTER TABLE answers DROP FOREIGN KEY FK_50D0C6061E27F6BF');
        $this->addSql('ALTER TABLE answers ADD CONSTRAINT FK_50D0C6061E27F6BF FOREIGN KEY (question_id) REFERENCES quiz_questions (id)');
        $this->addSql('ALTER TABLE quiz_test DROP FOREIGN KEY FK_128E82571E27F6BF');
        $this->addSql('ALTER TABLE quiz_test ADD CONSTRAINT FK_128E82571E27F6BF FOREIGN KEY (question_id) REFERENCES quiz_questions (id)');
        $this->addSql('DROP TABLE questions');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE TABLE questions (id INT AUTO_INCREMENT NOT NULL, level_id INT NOT NULL, topic_id INT NOT NULL, text VARCHAR(255) NOT NULL, is_active TINYINT(1) NOT NULL, INDEX IDX_8ADC54D55FB14BA7 (level_id), INDEX IDX_8ADC54D51F55203D (topic_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('INSERT INTO questions (id, level_id, topic_id, text, is_active) SELECT id, level_id, topic_id, text, is_active FROM quiz_questions');
        $this->addSql('ALTER TABLE questions ADD CONSTRAINT FK_8ADC54D51F55203D FOREIGN KEY (topic_id) REFERENCES topic (id)');
        $this->addSql('ALTER TABLE questions ADD CONSTRAINT FK_8ADC54D55FB14BA7 FOREIGN KEY (level_id) REFERENCES level (id)');
        $this->addSql('ALTER TABLE answers DROP FOREIGN KEY FK_50D0C6061E27F6BF');
        $this->addSql('ALTER TABLE answers ADD CONSTRAINT FK_50D0C6061E27F6BF FOREIGN KEY (question_id) REFERENCES questions (id)');
        $this->addSql('ALTER TABLE quiz_test DROP FOREIGN KEY FK_128E82571E27F6BF');
        $this->addSql('ALTER TABLE quiz_test ADD CONSTRAINT FK_128E82571E27F6BF FOREIGN KEY (question_id) REFERENCES questions (id)');
        $this->addSql('DROP TABLE quiz_questions');
    }
}
