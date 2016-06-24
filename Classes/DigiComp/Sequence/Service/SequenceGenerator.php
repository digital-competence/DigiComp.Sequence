<?php
namespace DigiComp\Sequence\Service;

/*                                                                        *
 * This script belongs to the FLOW3 package "DigiComp.Sequence".          *
 *                                                                        *
 *                                                                        */

use Doctrine\DBAL\DBALException;
use TYPO3\Flow\Annotations as Flow;

/**
 * A SequenceNumber generator working for transactional databases
 *
 *
 * Thoughts: We could make the step-range configurable, and if > 1 we could return new keys immediately for this
 * request, as we "reserved" the space between.
 *
 * @Flow\Scope("singleton")
 */
class SequenceGenerator
{

    /**
     * @var \Doctrine\Common\Persistence\ObjectManager
     * @Flow\Inject
     */
    protected $entityManager;

    /**
     * @var \TYPO3\Flow\Reflection\ReflectionService
     * @Flow\Inject
     */
    protected $reflectionService;

    /**
     * @var \TYPO3\Flow\Log\SystemLoggerInterface
     * @Flow\Inject
     */
    protected $systemLogger;

    /**
     * @param string|object $type
     *
     * @throws \DigiComp\Sequence\Service\Exception
     * @return int
     */
    public function getNextNumberFor($type)
    {
        $type = $this->inferTypeFromSource($type);
        $count = $this->getLastNumberFor($type);

        //TODO: Check for maximal tries, or similar
        //TODO: Let increment be configurable per type
        do {
            $count = $count + 1;
        } while (!$this->validateFreeNumber($count, $type));
        return $count;
    }

    protected function validateFreeNumber($count, $type)
    {
        $em = $this->entityManager;
        /** @var $em \Doctrine\ORM\EntityManager */
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

    public function advanceTo($to, $type)
    {
        $type = $this->inferTypeFromSource($type);
        return ($this->validateFreeNumber($to, $type));
    }

    /**
     * @param string|object $type
     *
     * @return int
     */
    public function getLastNumberFor($type)
    {
        $type = $this->inferTypeFromSource($type);
        /** @var $em \Doctrine\ORM\EntityManager */
        $em = $this->entityManager;

        $result = $em->getConnection()->executeQuery(
            'SELECT MAX(number) AS count FROM digicomp_sequence_domain_model_insert WHERE type=:type',
            ['type' => $type]
        );
        $count = $result->fetchAll();
        $count = $count[0]['count'];
        return $count;
    }

    /**
     * @param string|object $stringOrObject
     *
     * @throws Exception
     * @return string
     */
    protected function inferTypeFromSource($stringOrObject) {
        if (is_object($stringOrObject)) {
            $stringOrObject = $this->reflectionService->getClassNameByObject($stringOrObject);
        }
        if (!$stringOrObject) {
            throw new Exception('No Type given');
        }
        return $stringOrObject;
    }
}
