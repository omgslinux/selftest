<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260301173131 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE answers (id INT AUTO_INCREMENT NOT NULL, question_id INT NOT NULL, text VARCHAR(255) NOT NULL, valid TINYINT(1) DEFAULT NULL, is_active TINYINT(1) NOT NULL, INDEX IDX_50D0C6061E27F6BF (question_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE category (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE level (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('INSERT INTO level (id, name) VALUES (1, \'Básico\'), (2, \'Intermedio\'), (3, \'Avanzado\')');
        $this->addSql('CREATE TABLE questions (id INT AUTO_INCREMENT NOT NULL, level_id INT NOT NULL, topic_id INT NOT NULL, text VARCHAR(255) NOT NULL, is_active TINYINT(1) NOT NULL, INDEX IDX_8ADC54D55FB14BA7 (level_id), INDEX IDX_8ADC54D51F55203D (topic_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE quiz (id INT AUTO_INCREMENT NOT NULL, topic_id INT NOT NULL, level_id INT NOT NULL, name VARCHAR(255) NOT NULL, INDEX IDX_A412FA921F55203D (topic_id), INDEX IDX_A412FA925FB14BA7 (level_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE quiz_test (id INT AUTO_INCREMENT NOT NULL, quiz_id INT NOT NULL, question_id INT NOT NULL, INDEX IDX_128E8257853CD175 (quiz_id), INDEX IDX_128E82571E27F6BF (question_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE quiz_test_answers (id INT AUTO_INCREMENT NOT NULL, test_id INT NOT NULL, answer_id INT NOT NULL, user VARCHAR(255) NOT NULL, INDEX IDX_5A6897351E5D0459 (test_id), INDEX IDX_5A689735AA334807 (answer_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE topic (id INT AUTO_INCREMENT NOT NULL, category_id INT NOT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, INDEX IDX_9D40DE1B12469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE answers ADD CONSTRAINT FK_50D0C6061E27F6BF FOREIGN KEY (question_id) REFERENCES questions (id)');
        $this->addSql('ALTER TABLE questions ADD CONSTRAINT FK_8ADC54D55FB14BA7 FOREIGN KEY (level_id) REFERENCES level (id)');
        $this->addSql('ALTER TABLE questions ADD CONSTRAINT FK_8ADC54D51F55203D FOREIGN KEY (topic_id) REFERENCES topic (id)');
        $this->addSql('ALTER TABLE quiz ADD CONSTRAINT FK_A412FA921F55203D FOREIGN KEY (topic_id) REFERENCES topic (id)');
        $this->addSql('ALTER TABLE quiz ADD CONSTRAINT FK_A412FA925FB14BA7 FOREIGN KEY (level_id) REFERENCES level (id)');
        $this->addSql('ALTER TABLE quiz_test ADD CONSTRAINT FK_128E8257853CD175 FOREIGN KEY (quiz_id) REFERENCES quiz (id)');
        $this->addSql('ALTER TABLE quiz_test ADD CONSTRAINT FK_128E82571E27F6BF FOREIGN KEY (question_id) REFERENCES questions (id)');
        $this->addSql('CREATE UNIQUE INDEX quiz_name_topic_level_idx ON quiz (name, topic_id, level_id)');
        $this->addSql('ALTER TABLE quiz_test_answers ADD CONSTRAINT FK_5A6897351E5D0459 FOREIGN KEY (test_id) REFERENCES quiz_test (id)');
        $this->addSql('ALTER TABLE quiz_test_answers ADD CONSTRAINT FK_5A689735AA334807 FOREIGN KEY (answer_id) REFERENCES answers (id)');
        $this->addSql('ALTER TABLE topic ADD CONSTRAINT FK_9D40DE1B12469DE2 FOREIGN KEY (category_id) REFERENCES category (id)');
        $this->addSql('CREATE UNIQUE INDEX topic_name_category_idx ON topic (name, category_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX topic_name_category_idx ON topic');
        $this->addSql('ALTER TABLE answers DROP FOREIGN KEY FK_50D0C6061E27F6BF');
        $this->addSql('ALTER TABLE questions DROP FOREIGN KEY FK_8ADC54D55FB14BA7');
        $this->addSql('ALTER TABLE questions DROP FOREIGN KEY FK_8ADC54D51F55203D');
        $this->addSql('ALTER TABLE quiz DROP FOREIGN KEY FK_A412FA921F55203D');
        $this->addSql('ALTER TABLE quiz DROP FOREIGN KEY FK_A412FA925FB14BA7');
        $this->addSql('ALTER TABLE quiz_test DROP FOREIGN KEY FK_128E8257853CD175');
        $this->addSql('ALTER TABLE quiz_test DROP FOREIGN KEY FK_128E82571E27F6BF');
        $this->addSql('DROP INDEX quiz_name_topic_level_idx ON quiz');
        $this->addSql('ALTER TABLE quiz_test_answers DROP FOREIGN KEY FK_5A6897351E5D0459');
        $this->addSql('ALTER TABLE quiz_test_answers DROP FOREIGN KEY FK_5A689735AA334807');
        $this->addSql('ALTER TABLE topic DROP FOREIGN KEY FK_9D40DE1B12469DE2');
        $this->addSql('DROP TABLE answers');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE level');
        $this->addSql('DROP TABLE questions');
        $this->addSql('DROP TABLE quiz');
        $this->addSql('DROP TABLE quiz_test');
        $this->addSql('DROP TABLE quiz_test_answers');
        $this->addSql('DROP TABLE topic');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
