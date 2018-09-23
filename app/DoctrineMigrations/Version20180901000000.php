<?php

namespace Application\Migrations;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20180901000000 extends AbstractMigration implements ContainerAwareInterface {

  use ContainerAwareTrait;

  /**
   * @param Schema $schema
   */
  public function up(Schema $schema) {
    $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
    $prefix = $this->container->getParameter('database_table_prefix');
    $this->addSql('CREATE TABLE '.$prefix.'sessions (sess_id VARBINARY(128) NOT NULL, sess_time BIGINT NOT NULL, sess_lifetime INT NOT NULL, sess_data LONGBLOB NOT NULL, PRIMARY KEY(sess_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
    // Create new table if fresh install
    $this->addSql('CREATE TABLE IF NOT EXISTS '.$prefix.'cryptopaste (id BIGINT AUTO_INCREMENT NOT NULL, timestamp BIGINT NOT NULL, expiry BIGINT NOT NULL, views INT DEFAULT 0 NOT NULL, data LONGBLOB NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
    // Backwards compatibility for v0.x.x upgrade
    $this->addSql('RENAME TABLE '.$prefix.'cryptopaste TO '.$prefix.'pastes');
    $this->addSql('ALTER TABLE '.$prefix.'pastes CHANGE id id BIGINT AUTO_INCREMENT NOT NULL, CHANGE timestamp timestamp BIGINT NOT NULL, CHANGE expiry expiry BIGINT NOT NULL, CHANGE data data LONGBLOB NOT NULL');
  }

  /**
   * @param Schema $schema
   */
  public function down(Schema $schema) {
    $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
    $prefix = $this->container->getParameter('database_table_prefix');
    // Backwards compatibility for v0.x.x downgrade
    $this->addSql('RENAME TABLE '.$prefix.'pastes TO '.$prefix.'cryptopaste');
    $this->addSql('ALTER TABLE '.$prefix.'cryptopaste CHANGE id id INT AUTO_INCREMENT NOT NULL, CHANGE timestamp timestamp INT NOT NULL, CHANGE expiry expiry INT NOT NULL, CHANGE data data MEDIUMTEXT NOT NULL COLLATE utf8mb4_general_ci');
    // Note: This does not drop the old 'cryptopaste' table in case you are downgrading back to v0.x.x
    $this->addSql('DROP TABLE '.$prefix.'sessions');
  }
}

// EOF
