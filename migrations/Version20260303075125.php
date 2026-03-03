<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260303075125 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE quiz_test_answers DROP FOREIGN KEY FK_5A689735AA334807');
        $this->addSql('CREATE TABLE quiz_question_answers (id INT AUTO_INCREMENT NOT NULL, quiz_question_id INT NOT NULL, text VARCHAR(255) NOT NULL, valid TINYINT(1) DEFAULT NULL, is_active TINYINT(1) NOT NULL, INDEX IDX_425B76C33101E51F (quiz_question_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE quiz_question_answers ADD CONSTRAINT FK_425B76C33101E51F FOREIGN KEY (quiz_question_id) REFERENCES quiz_questions (id)');
        $this->addSql('ALTER TABLE answers DROP FOREIGN KEY FK_50D0C6061E27F6BF');
        $this->addSql('DROP TABLE answers');
        $this->addSql('ALTER TABLE quiz_questions DROP FOREIGN KEY FK_8CBC25331F55203D');
        $this->addSql('DROP INDEX IDX_8CBC25331F55203D ON quiz_questions');
        $this->addSql('ALTER TABLE quiz_questions CHANGE topic_id quiz_id INT NOT NULL');
        $this->addSql('ALTER TABLE quiz_questions ADD CONSTRAINT FK_8CBC2533853CD175 FOREIGN KEY (quiz_id) REFERENCES quiz (id)');
        $this->addSql('CREATE INDEX IDX_8CBC2533853CD175 ON quiz_questions (quiz_id)');
        $this->addSql('DROP INDEX IDX_5A689735AA334807 ON quiz_test_answers');
        $this->addSql('ALTER TABLE quiz_test_answers CHANGE answer_id quiz_question_answer_id INT NOT NULL');
        $this->addSql('ALTER TABLE quiz_test_answers ADD CONSTRAINT FK_5A689735D7DFCA0B FOREIGN KEY (quiz_question_answer_id) REFERENCES quiz_question_answers (id)');
        $this->addSql('CREATE INDEX IDX_5A689735D7DFCA0B ON quiz_test_answers (quiz_question_answer_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE quiz_test_answers DROP FOREIGN KEY FK_5A689735D7DFCA0B');
        $this->addSql('CREATE TABLE answers (id INT AUTO_INCREMENT NOT NULL, question_id INT NOT NULL, text VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, valid TINYINT(1) DEFAULT NULL, is_active TINYINT(1) NOT NULL, INDEX IDX_50D0C6061E27F6BF (question_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE answers ADD CONSTRAINT FK_50D0C6061E27F6BF FOREIGN KEY (question_id) REFERENCES quiz_questions (id)');
        $this->addSql('ALTER TABLE quiz_question_answers DROP FOREIGN KEY FK_425B76C33101E51F');
        $this->addSql('DROP TABLE quiz_question_answers');
        $this->addSql('ALTER TABLE quiz_questions DROP FOREIGN KEY FK_8CBC2533853CD175');
        $this->addSql('DROP INDEX IDX_8CBC2533853CD175 ON quiz_questions');
        $this->addSql('ALTER TABLE quiz_questions CHANGE quiz_id topic_id INT NOT NULL');
        $this->addSql('ALTER TABLE quiz_questions ADD CONSTRAINT FK_8CBC25331F55203D FOREIGN KEY (topic_id) REFERENCES topic (id)');
        $this->addSql('CREATE INDEX IDX_8CBC25331F55203D ON quiz_questions (topic_id)');
        $this->addSql('DROP INDEX IDX_5A689735D7DFCA0B ON quiz_test_answers');
        $this->addSql('ALTER TABLE quiz_test_answers CHANGE quiz_question_answer_id answer_id INT NOT NULL');
        $this->addSql('ALTER TABLE quiz_test_answers ADD CONSTRAINT FK_5A689735AA334807 FOREIGN KEY (answer_id) REFERENCES answers (id)');
        $this->addSql('CREATE INDEX IDX_5A689735AA334807 ON quiz_test_answers (answer_id)');
    }
}
