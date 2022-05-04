<?php

declare(strict_types=1);

namespace DigiComp\Sequence\Command;

use DigiComp\Sequence\Domain\Model\SequenceEntry;
use DigiComp\Sequence\Service\Exception\InvalidSourceException;
use DigiComp\Sequence\Service\SequenceGenerator;
use Doctrine\DBAL\Driver\Exception as DoctrineDBALDriverException;
use Doctrine\DBAL\Exception as DoctrineDBALException;
use Doctrine\ORM\EntityManagerInterface;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Cli\CommandController;

/**
 * @Flow\Scope("singleton")
 */
class SequenceCommandController extends CommandController
{
    /**
     * @Flow\Inject
     * @var SequenceGenerator
     */
    protected $sequenceGenerator;

    /**
     * @Flow\Inject
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * Set last number for sequence generator.
     *
     * @param string $type
     * @param int $number
     * @throws DoctrineDBALDriverException
     * @throws DoctrineDBALException
     * @throws InvalidSourceException
     */
    public function setLastNumberForCommand(string $type, int $number): void
    {
        if ($this->sequenceGenerator->setLastNumberFor($type, $number)) {
            $this->outputLine('Last number successfully set.');
        } else {
            $this->outputLine('Failed to set last number.');
        }
    }

    /**
     * Clean up sequence table.
     *
     * @param string[] $types
     * @throws DoctrineDBALDriverException
     * @throws DoctrineDBALException
     * @throws InvalidSourceException
     */
    public function cleanUpCommand(array $types = []): void
    {
        if ($types === []) {
            foreach (
                $this
                    ->entityManager
                    ->createQuery('SELECT DISTINCT(se.type) type FROM ' . SequenceEntry::class . ' se')
                    ->execute()
                as $result
            ) {
                $types[] = $result['type'];
            }
        }

        foreach ($types as $type) {
            $rowCount = $this
                ->entityManager
                ->createQuery('DELETE FROM ' . SequenceEntry::class . ' se WHERE se.type = ?0 AND se.number < ?1')
                ->execute([$type, $this->sequenceGenerator->getLastNumberFor($type)]);

            $this->outputLine('Deleted ' . $rowCount . ' row(s) for type "' . $type . '".');
        }
    }
}
