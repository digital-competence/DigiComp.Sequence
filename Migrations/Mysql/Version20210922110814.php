<?php

namespace Neos\Flow\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Exception as DoctrineDBALException;
use Doctrine\DBAL\Migrations\AbortMigrationException;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20210922110814 extends AbstractMigration
{
    /**
     * @param Schema $schema
     * @throws AbortMigrationException
     * @throws DoctrineDBALException
     */
    public function up(Schema $schema): void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on "mysql".');

        $this->addSql('CREATE TABLE digicomp_sequence_domain_model_sequenceentry (persistence_object_identifier VARCHAR(40) NOT NULL, type VARCHAR(255) NOT NULL, number INT NOT NULL, INDEX IDX_F6ADC8568CDE5729 (type), UNIQUE INDEX UNIQ_F6ADC8568CDE572996901F54 (type, number), PRIMARY KEY(persistence_object_identifier))');
        $this->addSql('INSERT INTO digicomp_sequence_domain_model_sequenceentry (persistence_object_identifier, type, number) SELECT UUID(), i.type, i.number FROM digicomp_sequence_domain_model_insert AS i');
        $this->addSql('DROP TABLE digicomp_sequence_domain_model_insert');
    }

    /**
     * @param Schema $schema
     * @throws AbortMigrationException
     * @throws DoctrineDBALException
     */
    public function down(Schema $schema): void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on "mysql".');

        $this->addSql('CREATE TABLE digicomp_sequence_domain_model_insert (number INT NOT NULL, type VARCHAR(255) NOT NULL, INDEX type_idx (type), PRIMARY KEY(number, type))');
        $this->addSql('INSERT INTO digicomp_sequence_domain_model_insert (number, type) SELECT se.number, se.type FROM digicomp_sequence_domain_model_sequenceentry AS se');
        $this->addSql('DROP TABLE digicomp_sequence_domain_model_sequenceentry');
    }
}
