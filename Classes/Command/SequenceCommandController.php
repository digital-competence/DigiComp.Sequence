<?php

declare(strict_types=1);

namespace DigiComp\Sequence\Command;

use DigiComp\Sequence\Domain\Model\Insert;
use DigiComp\Sequence\Service\SequenceGenerator;
use Doctrine\ORM\EntityManagerInterface;
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
     * @Flow\Inject
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * Sets minimum number for sequence generator
     *
     * @param int $to
     * @param string $type
     */
    public function advanceCommand(int $to, string $type): void
    {
        $this->sequenceGenerator->advanceTo($to, $type);
    }

    /**
     * @param string[] $typesToClean
     */
    public function cleanSequenceInsertsCommand(array $typesToClean = [])
    {
        $cleanArray = [];
        if (empty($typesToClean)) {
            $results = $this->entityManager
                ->createQuery('SELECT i.type, MAX(i.number) max_number FROM ' . Insert::class . ' i GROUP BY i.type')
                ->getScalarResult();
            foreach ($results as $result) {
                $cleanArray[$result['type']] = (int) $result['max_number'];
            }
        } else {
            foreach ($typesToClean as $typeToClean) {
                $cleanArray[$typeToClean] = $this->sequenceGenerator->getLastNumberFor($typeToClean);
            }
        }
        foreach ($cleanArray as $typeToClean => $number) {
            $this->entityManager
                ->createQuery('DELETE FROM ' . Insert::class . ' i WHERE i.type = ?0 AND i.number < ?1')
                ->execute([$typeToClean, $number]);
        }
    }
}
