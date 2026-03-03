<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260303091314 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE quiz_test DROP FOREIGN KEY FK_128E82571E27F6BF');
        $this->addSql('DROP INDEX IDX_128E82571E27F6BF ON quiz_test');
        $this->addSql('ALTER TABLE quiz_test ADD created_at DATETIME NOT NULL, ADD updated_at DATETIME NOT NULL, CHANGE question_id user_id INT NOT NULL');
        $this->addSql('ALTER TABLE quiz_test ADD CONSTRAINT FK_128E8257A76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('CREATE INDEX IDX_128E8257A76ED395 ON quiz_test (user_id)');
        $this->addSql('ALTER TABLE quiz_test_answers DROP FOREIGN KEY FK_5A6897351E5D0459');
        $this->addSql('ALTER TABLE quiz_test_answers DROP FOREIGN KEY FK_5A689735D7DFCA0B');
        $this->addSql('DROP INDEX IDX_5A6897351E5D0459 ON quiz_test_answers');
        $this->addSql('DROP INDEX IDX_5A689735D7DFCA0B ON quiz_test_answers');
        $this->addSql('ALTER TABLE quiz_test_answers ADD quiz_test_id INT NOT NULL, ADD questions JSON NOT NULL COMMENT \'(DC2Type:json)\', ADD answers JSON NOT NULL COMMENT \'(DC2Type:json)\', DROP test_id, DROP quiz_question_answer_id, DROP user');
        $this->addSql('ALTER TABLE quiz_test_answers ADD CONSTRAINT FK_5A689735C07A9F9 FOREIGN KEY (quiz_test_id) REFERENCES quiz_test (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5A689735C07A9F9 ON quiz_test_answers (quiz_test_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE quiz_test DROP FOREIGN KEY FK_128E8257A76ED395');
        $this->addSql('DROP INDEX IDX_128E8257A76ED395 ON quiz_test');
        $this->addSql('ALTER TABLE quiz_test DROP created_at, DROP updated_at, CHANGE user_id question_id INT NOT NULL');
        $this->addSql('ALTER TABLE quiz_test ADD CONSTRAINT FK_128E82571E27F6BF FOREIGN KEY (question_id) REFERENCES quiz_questions (id)');
        $this->addSql('CREATE INDEX IDX_128E82571E27F6BF ON quiz_test (question_id)');
        $this->addSql('ALTER TABLE quiz_test_answers DROP FOREIGN KEY FK_5A689735C07A9F9');
        $this->addSql('DROP INDEX UNIQ_5A689735C07A9F9 ON quiz_test_answers');
        $this->addSql('ALTER TABLE quiz_test_answers ADD quiz_question_answer_id INT NOT NULL, ADD user VARCHAR(255) NOT NULL, DROP questions, DROP answers, CHANGE quiz_test_id test_id INT NOT NULL');
        $this->addSql('ALTER TABLE quiz_test_answers ADD CONSTRAINT FK_5A6897351E5D0459 FOREIGN KEY (test_id) REFERENCES quiz_test (id)');
        $this->addSql('ALTER TABLE quiz_test_answers ADD CONSTRAINT FK_5A689735D7DFCA0B FOREIGN KEY (quiz_question_answer_id) REFERENCES quiz_question_answers (id)');
        $this->addSql('CREATE INDEX IDX_5A6897351E5D0459 ON quiz_test_answers (test_id)');
        $this->addSql('CREATE INDEX IDX_5A689735D7DFCA0B ON quiz_test_answers (quiz_question_answer_id)');
    }
}
