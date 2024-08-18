<?php declare(strict_types=1);
namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;


final class Version20240105082853 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }


    public function up(Schema $schema): void
    {
        $this->addSql('SET foreign_key_checks = 0');

        $this->addSql('DROP TABLE IF EXISTS article');
        $this->addSql('DROP TABLE IF EXISTS image');
        $this->addSql('DROP TABLE IF EXISTS tag');
        $this->addSql('DROP TABLE IF EXISTS file');
        $this->addSql('DROP TABLE IF EXISTS badge');

        $this->addSql('DROP TABLE IF EXISTS article_author');
        $this->addSql('DROP TABLE IF EXISTS image_author');
        $this->addSql('DROP TABLE IF EXISTS tag_author');
        $this->addSql('DROP TABLE IF EXISTS file_author');

        $this->addSql('DROP TABLE IF EXISTS article_image');
        $this->addSql('DROP TABLE IF EXISTS article_tag');
        $this->addSql('DROP TABLE IF EXISTS article_file');
        $this->addSql('DROP TABLE IF EXISTS tag_badge');
        
        $this->addSql('DROP TABLE IF EXISTS newsletter_opener');
        $this->addSql('DROP TABLE IF EXISTS newsletter_expiring_warn');
        
        $this->addSql('SET foreign_key_checks = 1');


        // ARTICLES
        $this->addSql('CREATE TABLE article (id INT UNSIGNED AUTO_INCREMENT NOT NULL, title VARCHAR(512) NOT NULL, format SMALLINT UNSIGNED NOT NULL, publishing_status SMALLINT UNSIGNED NOT NULL, comments_topic_id INT UNSIGNED DEFAULT NULL, spotlight_id INT UNSIGNED DEFAULT NULL, views INT UNSIGNED NOT NULL, show_ads TINYINT(1) NOT NULL, abstract VARCHAR(2000) DEFAULT NULL, body LONGTEXT DEFAULT NULL, published_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_23A0E663049EF9 (spotlight_id), INDEX IDX_23A0E668D8A6755 (comments_topic_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // IMAGES
        $this->addSql('CREATE TABLE image (id INT UNSIGNED AUTO_INCREMENT NOT NULL, title VARCHAR(512) NOT NULL, format VARCHAR(5) NOT NULL, watermark_position SMALLINT UNSIGNED NOT NULL, reusable TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // TAGS
        $this->addSql('CREATE TABLE tag (id INT UNSIGNED AUTO_INCREMENT NOT NULL, title VARCHAR(512) NOT NULL, ranking SMALLINT UNSIGNED NOT NULL, views INT UNSIGNED NOT NULL, abstract VARCHAR(2000) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // FILES
        $this->addSql('CREATE TABLE file (id INT UNSIGNED AUTO_INCREMENT NOT NULL, title VARCHAR(512) NOT NULL, format VARCHAR(15) DEFAULT NULL, url VARCHAR(2500) DEFAULT NULL, views INT UNSIGNED NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // BADGES
        $this->addSql('CREATE TABLE badge (id INT UNSIGNED AUTO_INCREMENT NOT NULL, title VARCHAR(512) NOT NULL, image_url VARCHAR(1024) DEFAULT NULL, user_selectable TINYINT(1) NOT NULL, abstract VARCHAR(2000) DEFAULT NULL, body LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');



        // 🔗 ARTICLES <-> AUTHORS
        $this->addSql('CREATE TABLE article_author (id INT UNSIGNED AUTO_INCREMENT NOT NULL, article_id INT UNSIGNED NOT NULL, user_id INT UNSIGNED NOT NULL, ranking SMALLINT UNSIGNED NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_D7684F487294869C (article_id), INDEX IDX_D7684F48A76ED395 (user_id), UNIQUE INDEX same_article_same_author_unique_idx (article_id, user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // 🔗 IMAGES <-> AUTHORS
        $this->addSql('CREATE TABLE image_author (id INT UNSIGNED AUTO_INCREMENT NOT NULL, image_id INT UNSIGNED NOT NULL, user_id INT UNSIGNED NOT NULL, ranking SMALLINT UNSIGNED NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_12B286003DA5256D (image_id), INDEX IDX_12B28600A76ED395 (user_id), UNIQUE INDEX same_image_same_author_unique_idx (image_id, user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // 🔗 TAGS <-> AUTHORS
        $this->addSql('CREATE TABLE tag_author (id INT UNSIGNED AUTO_INCREMENT NOT NULL, tag_id INT UNSIGNED NOT NULL, user_id INT UNSIGNED NOT NULL, ranking SMALLINT UNSIGNED NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_54EE91E4BAD26311 (tag_id), INDEX IDX_54EE91E4A76ED395 (user_id), UNIQUE INDEX same_tag_same_author_unique_idx (tag_id, user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // 🔗 FILES <-> AUTHORS
        $this->addSql('CREATE TABLE file_author (id INT UNSIGNED AUTO_INCREMENT NOT NULL, file_id INT UNSIGNED NOT NULL, user_id INT UNSIGNED NOT NULL, ranking SMALLINT UNSIGNED NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_5B8FE7793CB796C (file_id), INDEX IDX_5B8FE77A76ED395 (user_id), UNIQUE INDEX same_file_same_author_unique_idx (file_id, user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');



        // 🔗 ARTICLES <-> IMAGES
        $this->addSql('CREATE TABLE article_image (id INT UNSIGNED AUTO_INCREMENT NOT NULL, article_id INT UNSIGNED NOT NULL, image_id INT UNSIGNED NOT NULL, ranking SMALLINT UNSIGNED NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_B28A764E7294869C (article_id), INDEX IDX_B28A764E3DA5256D (image_id), UNIQUE INDEX same_article_same_image_unique_idx (article_id, image_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // 🔗 ARTICLES <-> TAGS
        $this->addSql('CREATE TABLE article_tag (id INT UNSIGNED AUTO_INCREMENT NOT NULL, article_id INT UNSIGNED NOT NULL, tag_id INT UNSIGNED NOT NULL, user_id INT UNSIGNED NOT NULL, ranking SMALLINT UNSIGNED NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_919694F97294869C (article_id), INDEX IDX_919694F9BAD26311 (tag_id), INDEX IDX_919694F9A76ED395 (user_id), UNIQUE INDEX same_article_same_tag_unique_idx (article_id, tag_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');


        // 🔗 ARTICLES <-> FILES
        $this->addSql('CREATE TABLE article_file (id INT UNSIGNED AUTO_INCREMENT NOT NULL, article_id INT UNSIGNED NOT NULL, file_id INT UNSIGNED NOT NULL, user_id INT UNSIGNED NOT NULL, ranking SMALLINT UNSIGNED NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_3CDDB1117294869C (article_id), INDEX IDX_3CDDB11193CB796C (file_id), INDEX IDX_3CDDB111A76ED395 (user_id), UNIQUE INDEX same_article_same_file_unique_idx (article_id, file_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // 🔗 TAGS <-> BADGES
        $this->addSql('CREATE TABLE tag_badge (id INT UNSIGNED AUTO_INCREMENT NOT NULL, tag_id INT UNSIGNED NOT NULL, badge_id INT UNSIGNED NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_DC1C511BBAD26311 (tag_id), INDEX IDX_DC1C511BF7A2C2FC (badge_id), UNIQUE INDEX same_tag_same_badge_unique_idx (tag_id, badge_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');



        // NEWSLETTER
        $this->addSql('CREATE TABLE newsletter_opener (id INT UNSIGNED AUTO_INCREMENT NOT NULL, user_id INT UNSIGNED NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX user_unique_idx (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE newsletter_expiring_warn (id INT UNSIGNED AUTO_INCREMENT NOT NULL, user_id INT UNSIGNED NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX user_unique_idx (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        


        // FKs
        $this->addSql('ALTER TABLE article ADD CONSTRAINT FK_23A0E663049EF9 FOREIGN KEY (spotlight_id) REFERENCES image (id)');
        $this->addSql('ALTER TABLE article_author ADD CONSTRAINT FK_D7684F487294869C FOREIGN KEY (article_id) REFERENCES article (id)');
        $this->addSql('ALTER TABLE article_file ADD CONSTRAINT FK_3CDDB1117294869C FOREIGN KEY (article_id) REFERENCES article (id)');
        $this->addSql('ALTER TABLE article_file ADD CONSTRAINT FK_3CDDB11193CB796C FOREIGN KEY (file_id) REFERENCES file (id)');
        $this->addSql('ALTER TABLE article_image ADD CONSTRAINT FK_B28A764E7294869C FOREIGN KEY (article_id) REFERENCES article (id)');
        $this->addSql('ALTER TABLE article_image ADD CONSTRAINT FK_B28A764E3DA5256D FOREIGN KEY (image_id) REFERENCES image (id)');
        $this->addSql('ALTER TABLE article_tag ADD CONSTRAINT FK_919694F97294869C FOREIGN KEY (article_id) REFERENCES article (id)');
        $this->addSql('ALTER TABLE article_tag ADD CONSTRAINT FK_919694F9BAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id)');
        $this->addSql('ALTER TABLE file_author ADD CONSTRAINT FK_5B8FE7793CB796C FOREIGN KEY (file_id) REFERENCES file (id)');
        $this->addSql('ALTER TABLE image_author ADD CONSTRAINT FK_12B286003DA5256D FOREIGN KEY (image_id) REFERENCES image (id)');
        $this->addSql('ALTER TABLE tag_author ADD CONSTRAINT FK_54EE91E4BAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id)');
        $this->addSql('ALTER TABLE tag_badge ADD CONSTRAINT FK_DC1C511BBAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id)');
        $this->addSql('ALTER TABLE tag_badge ADD CONSTRAINT FK_DC1C511BF7A2C2FC FOREIGN KEY (badge_id) REFERENCES badge (id)');
    }


    public function down(Schema $schema): void { }
}
