<?php

declare(strict_types=1);

namespace DigiComp\Sequence\Service;

use DigiComp\Sequence\Domain\Model\SequenceEntry;
use DigiComp\Sequence\Service\Exception\InvalidSourceException;
use Doctrine\DBAL\Driver\Exception as DoctrineDBALDriverException;
use Doctrine\DBAL\Exception as DoctrineDBALException;
use Doctrine\ORM\EntityManagerInterface;
use Neos\Flow\Annotations as Flow;
use Neos\Utility\TypeHandling;
use Psr\Log\LoggerInterface;

/**
 * A sequence number generator working for transactional databases.
 *
 * Thoughts: We could make the step-range configurable, and if > 1 we could return new keys immediately for this
 * request, as we "reserved" the space between.
 *
 * @Flow\Scope("singleton")
 */
class SequenceGenerator
{
    /**
     * @Flow\Inject
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @Flow\Inject
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param string|object $source
     * @return int
     * @throws DoctrineDBALDriverException
     * @throws DoctrineDBALException
     * @throws InvalidSourceException
     */
    public function getNextNumberFor($source): int
    {
        $type = $this->inferTypeFromSource($source);
        $number = $this->getLastNumberFor($type);

        // TODO: Check for maximal tries, or similar?
        // TODO: Let increment be configurable per type?
        do {
            $number++;
        } while (!$this->insertFor($type, $number));

        return $number;
    }

    /**
     * @param string $type
     * @param int $number
     * @return bool
     */
    protected function insertFor(string $type, int $number): bool
    {
        try {
            $this->entityManager->getConnection()->insert(
                $this->entityManager->getClassMetadata(SequenceEntry::class)->getTableName(),
                ['type' => $type, 'number' => $number]
            );

            return true;
        } catch (\PDOException $exception) {
        } catch (DoctrineDBALException $exception) {
            if (!$exception->getPrevious() instanceof \PDOException) {
                $this->logger->critical('Exception occurred: ' . $exception->getMessage());
            }
        } catch (\Exception $exception) {
            $this->logger->critical('Exception occurred: ' . $exception->getMessage());
        }

        return false;
    }

    /**
     * @param string|object $source
     * @param int $number
     * @return bool
     * @throws DoctrineDBALDriverException
     * @throws DoctrineDBALException
     * @throws InvalidSourceException
     */
    public function setLastNumberFor($source, int $number): bool
    {
        $type = $this->inferTypeFromSource($source);

        if ($this->getLastNumberFor($type) >= $number) {
            return false;
        }

        return $this->insertFor($type, $number);
    }

    /**
     * @param string|object $source
     * @throws DoctrineDBALDriverException
     * @throws DoctrineDBALException
     * @throws InvalidSourceException
     */
    public function getLastNumberFor($source): int
    {
        return (int)$this->entityManager->getConnection()->executeQuery(
            'SELECT MAX(number) FROM '
            . $this->entityManager->getClassMetadata(SequenceEntry::class)->getTableName()
            . ' WHERE type = :type',
            ['type' => $this->inferTypeFromSource($source)]
        )->fetchOne();
    }

    /**
     * @param string|object $source
     * @return string
     * @throws InvalidSourceException
     */
    protected function inferTypeFromSource($source): string
    {
        if (\is_string($source)) {
            return $source;
        }

        if (\is_object($source)) {
            return TypeHandling::getTypeForValue($source);
        }

        throw new InvalidSourceException('Could not infer type from source.', 1632216173);
    }
}
