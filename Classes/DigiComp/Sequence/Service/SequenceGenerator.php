<?php
namespace DigiComp\Sequence\Service;

/*                                                                        *
 * This script belongs to the FLOW3 package "DigiComp.Sequence".          *
 *                                                                        *
 *                                                                        */

use Doctrine\DBAL\DBALException;
use TYPO3\Flow\Annotations as Flow;

/**
 * A SequenceNumber generator (should be DB-agnostic)
 *
 * @Flow\Scope("singleton")
 */
class SequenceGenerator {

	/**
	 * @var \Doctrine\Common\Persistence\ObjectManager
	 * @Flow\Inject
	 */
	protected $_em;

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
	 * @throws \DigiComp\Sequence\Service\Exception
	 * @return int
	 */
	public function getNextNumberFor($type) {
		if (is_object($type)) {
			$type = $this->reflectionService->getClassNameByObject($type);
		}
		if (!$type) {
			throw new Exception('No Type given');
		}
		$count = $this->getLastNumberFor($type);

		//TODO: Check for maximal tries, or similar
		do {
			$count = $count+1;
		} while (! $this->validateFreeNumber($count, $type));
		return $count;
	}

	protected function validateFreeNumber($count, $type) {
		$em = $this->_em;
		/** @var $em \Doctrine\ORM\EntityManager */
		try {
			$em->getConnection()->insert('digicomp_sequence_domain_model_insert', array('number' => $count, 'type' => $type));
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

	public function advanceTo($to, $type) {
		return ($this->validateFreeNumber($to, $type));
	}

	/**
	 * @param $type
	 * @return int
	 */
	public function getLastNumberFor($type) {
		/** @var $em \Doctrine\ORM\EntityManager */
		$em = $this->_em;

		$result = $em->getConnection()->executeQuery('SELECT MAX(number) AS count FROM digicomp_sequence_domain_model_insert WHERE type=:type', array('type' => $type));
		$count = $result->fetchAll();
		$count = $count[0]['count'];
		return $count;
	}

}