<?php
namespace Neos\Flow\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration,
    Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your need!
 */
class Version20140505093853 extends AbstractMigration
{

    /**
     * @param Schema $schema
     *
     * @return void
     */
    public function up(Schema $schema)
    {
        // this up() migration is autogenerated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");

        $this->addSql(
            "CREATE TABLE digicomp_sequence_domain_model_insert (number INT NOT NULL, type VARCHAR(255) NOT NULL, PRIMARY KEY(number, type)) ENGINE = InnoDB"
        );
    }

    /**
     * @param Schema $schema
     *
     * @return void
     */
    public function down(Schema $schema)
    {
        // this down() migration is autogenerated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "mysql");

        $this->addSql("DROP TABLE digicomp_sequence_domain_model_insert");
    }
}
