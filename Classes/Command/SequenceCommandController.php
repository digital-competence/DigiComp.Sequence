<?php
namespace DigiComp\Sequence\Command;

use DigiComp\Sequence\Service\SequenceGenerator;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Cli\CommandController;

/**
 * A database agnostic SequenceNumber generator
 *
 * @Flow\Scope("singleton")
 */
class SequenceCommandController extends CommandController
{
    /**
     * @var SequenceGenerator
     * @Flow\Inject
     */
    protected $sequenceGenerator;

    /**
     * Sets minimum number for sequence generator
     *
     * @param int $to
     * @param string $type
     */
    public function advanceCommand($to, $type)
    {
        $this->sequenceGenerator->advanceTo($to, $type);
    }

    // TODO: make clean up job to delete all but the biggest number to save resources
}
