<?php
namespace DigiComp\Sequence\Command;

/*                                                                        *
 * This script belongs to the FLOW3 package "DigiComp.Sequence".          *
 *                                                                        *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Cli\CommandController;

/**
 * A database agnostic SequenceNumber generator
 *
 * @Flow\Scope("singleton")
 */
class SequenceCommandController extends CommandController
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

    //TODO: make clean up job to delete all but the biggest number to save resources
}
