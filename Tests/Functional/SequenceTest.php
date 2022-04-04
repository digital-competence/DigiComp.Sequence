<?php

declare(strict_types=1);

namespace DigiComp\Sequence\Tests\Functional;

use DigiComp\Sequence\Service\Exception\InvalidSourceException;
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
     * @throws DoctrineDBALDriverException
     * @throws DoctrineDBALException
     * @throws InvalidSourceException
     */
    public function sequenceTest(): void
    {
        $sequenceGenerator = $this->objectManager->get(SequenceGenerator::class);

        $this->assertEquals(0, $sequenceGenerator->getLastNumberFor($sequenceGenerator));
        $this->assertEquals(1, $sequenceGenerator->getNextNumberFor($sequenceGenerator));

        $pIds = [];
        for ($i = 0; $i < 10; $i++) {
            $pId = \pcntl_fork();
            if ($pId > 0) {
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
     * @throws DoctrineDBALDriverException
     * @throws DoctrineDBALException
     * @throws InvalidSourceException
     */
    public function setLastNumberForTest(): void
    {
        $sequenceGenerator = $this->objectManager->get(SequenceGenerator::class);
        $sequenceGenerator->setLastNumberFor($sequenceGenerator, 100);

        $this->assertEquals(100, $sequenceGenerator->getLastNumberFor($sequenceGenerator));
        $this->assertEquals(0, $sequenceGenerator->getLastNumberFor('otherSequence'));
    }
}
