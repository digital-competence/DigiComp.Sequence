<?php
namespace DigiComp\Sequence\Service;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManager;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Log\SystemLoggerInterface;
use Neos\Flow\Reflection\ReflectionService;
use Neos\Utility\TypeHandling;

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
     * @var ObjectManager
     * @Flow\Inject
     */
    protected $entityManager;

    /**
     * @var ReflectionService
     * @Flow\Inject
     */
    protected $reflectionService;

    /**
     * @var SystemLoggerInterface
     * @Flow\Inject
     */
    protected $systemLogger;

    /**
     * @param string|object $type
     *
     * @return int
     */
    public function getNextNumberFor($type)
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
     * @param string|object $type
     *
     * @return bool
     */
    protected function validateFreeNumber($count, $type)
    {
        /** @var EntityManager $em */
        $em = $this->entityManager;
        try {
            $em->getConnection()->insert(
                'digicomp_sequence_domain_model_insert',
                ['number' => $count, 'type' => $type]
            );
            return true;
        } catch (\PDOException $e) {
            return false;
        } catch (DBALException $e) {
            if ($e->getPrevious() && $e->getPrevious() instanceof \PDOException) {
                // Do nothing, new Doctrine handling hides the above error
            } else {
                $this->systemLogger->logException($e);
            }
        } catch (\Exception $e) {
            $this->systemLogger->logException($e);
        }

        return false;
    }

    /**
     * @param int $to
     * @param string|object $type
     *
     * @return bool
     */
    public function advanceTo($to, $type)
    {
        $type = $this->inferTypeFromSource($type);

        return $this->validateFreeNumber($to, $type);
    }

    /**
     * @param string|object $type
     *
     * @return int
     */
    public function getLastNumberFor($type)
    {
        /** @var EntityManager $em */
        $em = $this->entityManager;

        $result = $em->getConnection()->executeQuery(
            'SELECT MAX(number) AS count FROM digicomp_sequence_domain_model_insert WHERE type=:type',
            ['type' => $this->inferTypeFromSource($type)]
        );
        $count = $result->fetchAll();
        $count = $count[0]['count'];

        return $count;
    }

    /**
     * @param string|object $stringOrObject
     *
     * @return string
     * @throws Exception
     */
    protected function inferTypeFromSource($stringOrObject)
    {
        if (is_object($stringOrObject)) {
            $stringOrObject = TypeHandling::getTypeForValue($stringOrObject);
        }
        if (! $stringOrObject) {
            throw new Exception('No Type given');
        }

        return $stringOrObject;
    }
}
