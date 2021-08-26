<?php

declare(strict_types=1);

namespace DigiComp\Sequence\Service;

use DigiComp\Sequence\Domain\Model\Insert;
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\ORM\EntityManagerInterface;
use Neos\Flow\Annotations as Flow;
use Neos\Utility\TypeHandling;
use Psr\Log\LoggerInterface;

/**
 * A SequenceNumber generator working for transactional databases
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
    protected $systemLogger;

    /**
     * @param string|object $type
     *
     * @return int
     * @throws Exception
     * @throws DBALException
     */
    public function getNextNumberFor($type): int
    {
        $type = $this->inferTypeFromSource($type);
        $count = $this->getLastNumberFor($type);

        // TODO: Check for maximal tries, or similar
        // TODO: Let increment be configurable per type
        do {
            $count++;
        } while (! $this->validateFreeNumber($count, $type));

        return $count;
    }

    /**
     * @param int $count
     * @param string $type
     * @return bool
     */
    protected function validateFreeNumber(int $count, string $type): bool
    {
        $em = $this->entityManager;
        try {
            $em->getConnection()->insert(
                $em->getClassMetadata(Insert::class)->getTableName(),
                ['number' => $count, 'type' => $type]
            );
            return true;
        } catch (\PDOException $e) {
            return false;
        } catch (DBALException $e) {
            if (! $e->getPrevious() instanceof \PDOException) {
                $this->systemLogger->critical('Exception occured: ' . $e->getMessage());
            }
        } catch (\Exception $e) {
            $this->systemLogger->critical('Exception occured: ' . $e->getMessage());
        }

        return false;
    }

    /**
     * @param int $to
     * @param string|object $type
     *
     * @return bool
     * @throws Exception
     */
    public function advanceTo(int $to, $type): bool
    {
        $type = $this->inferTypeFromSource($type);

        return $this->validateFreeNumber($to, $type);
    }

    /**
     * @param string|object $type
     *
     * @return int
     * @throws Exception
     * @throws DBALException
     */
    public function getLastNumberFor($type): int
    {
        return (int) $this->entityManager->getConnection()->executeQuery(
            'SELECT MAX(number) FROM '
                . $this->entityManager->getClassMetadata(Insert::class)->getTableName()
                . ' WHERE type = :type',
            ['type' => $this->inferTypeFromSource($type)]
        )->fetchAll(\PDO::FETCH_COLUMN)[0];
    }

    /**
     * @param string|object $stringOrObject
     * @return string
     * @throws Exception
     */
    protected function inferTypeFromSource($stringOrObject): string
    {
        if (\is_object($stringOrObject)) {
            $stringOrObject = TypeHandling::getTypeForValue($stringOrObject);
        }
        if (! $stringOrObject) {
            throw new Exception('No Type given');
        }

        return $stringOrObject;
    }
}
