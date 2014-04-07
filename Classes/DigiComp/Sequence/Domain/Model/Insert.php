<?php
namespace DigiComp\Sequence\Domain\Model;

/*                                                                        *
 * This script belongs to the FLOW3 package "DigiComp.Sequence".          *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;
/**
 * SequenceInsert
 *
 * @author fcool
 * @Flow\Scope("prototype")
 * @Flow\Entity
 */
class Insert {

	/**
	 * @var int
	 * @ORM\Id
	 * @Flow\Identity
	 */
	protected $number;

	/**
	 * @var string
	 * @ORM\Id
	 * @Flow\Identity
	 */
	protected $type;

	/**
	 * @param int $number
	 * @param string $type
	 */
	public function __construct($number, $type) {
		$this->setType($type);
		$this->setNumber($number);
	}

	/**
	 * @param int $number
	 */
	public function setNumber($number) {
		$this->number = $number;
	}

	/**
	 * @return int
	 */
	public function getNumber() {
		return $this->number;
	}

	/**
	 * @param string|object $type
	 */
	public function setType($type) {
		if (is_object($type)) {
			$type = get_class($type);
		}
		$this->type = $type;
	}

	/**
	 * @return string
	 */
	public function getType() {
		return $this->type;
	}
}