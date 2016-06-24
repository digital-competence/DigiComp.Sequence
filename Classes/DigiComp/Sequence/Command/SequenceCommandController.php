<?php
namespace DigiComp\Sequence\Command;

/*                                                                        *
 * This script belongs to the FLOW3 package "DigiComp.Sequence".          *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;

/**
 * A SequenceNumber generator (should be DB-agnostic)
 *
 * @Flow\Scope("singleton")
 */
class SequenceCommandController extends \TYPO3\Flow\Cli\CommandController
{

    /**
     * @var \DigiComp\Sequence\Service\SequenceGenerator
     * @Flow\Inject
     */
    protected $sequenceGenerator;

    /**
     * Sets minimum number for sequence generator
     *
     * @param int    $to
     * @param string $type
     */
    public function advanceCommand($to, $type)
    {
        $this->sequenceGenerator->advanceTo($to, $type);
    }
}
