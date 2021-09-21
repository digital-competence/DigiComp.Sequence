<?php

namespace DigiComp\Sequence\Tests\Functional;

use DigiComp\Sequence\Service\Exception as DigiCompSequenceServiceException;
use DigiComp\Sequence\Service\SequenceGenerator;
use Doctrine\DBAL\Driver\Exception as DoctrineDBALDriverException;
use Doctrine\DBAL\Exception as DoctrineDBALException;
use Neos\Flow\Tests\FunctionalTestCase;

class SequenceTest extends FunctionalTestCase
{
    /**
     * @inheritDoc
     */
    protected static $testablePersistenceEnabled = true;

    /**
     * @test
     * @throws DigiCompSequenceServiceException
     * @throws DoctrineDBALDriverException
     * @throws DoctrineDBALException
     */
    public function sequenceTest()
    {
        $sequenceGenerator = $this->objectManager->get(SequenceGenerator::class);

        $number = $sequenceGenerator->getLastNumberFor($sequenceGenerator);
        $this->assertEquals(0, $number);
        $this->assertEquals(1, $sequenceGenerator->getNextNumberFor($sequenceGenerator));

        $pIds = [];
        for ($i = 0; $i < 10; $i++) {
            $pId = \pcntl_fork();
            if ($pId) {
                $pIds[] = $pId;
            } else {
                for ($j = 0; $j < 10; $j++) {
                    $sequenceGenerator->getNextNumberFor($sequenceGenerator);
                }
                // making a hard exit to avoid phpunit having the tables cleaned up again
                exit;
            }
        }

        foreach ($pIds as $pId) {
            $status = 0;
            \pcntl_waitpid($pId, $status);
        }

        $this->assertEquals(101, $sequenceGenerator->getLastNumberFor($sequenceGenerator));
    }

    /**
     * @test
     * @throws DigiCompSequenceServiceException
     * @throws DoctrineDBALDriverException
     * @throws DoctrineDBALException
     */
    public function advanceTest()
    {
        $sequenceGenerator = $this->objectManager->get(SequenceGenerator::class);

        $sequenceGenerator->advanceTo(100, $sequenceGenerator);
        $this->assertEquals(100, $sequenceGenerator->getLastNumberFor($sequenceGenerator));
        $this->assertEquals(0, $sequenceGenerator->getLastNumberFor('strangeOtherSequence'));
    }
}
