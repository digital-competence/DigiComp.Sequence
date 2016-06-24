<?php
namespace DigiComp\Sequence\Tests\Functional;

use DigiComp\Sequence\Service\SequenceGenerator;
use TYPO3\Flow\Tests\FunctionalTestCase;

class SequenceTest extends FunctionalTestCase
{

    protected static $testablePersistenceEnabled = true;

    /**
     * @test
     */
    public function sequenceTest()
    {
        $sequenceGenerator = $this->objectManager->get(SequenceGenerator::class);

        $number = $sequenceGenerator->getLastNumberFor($sequenceGenerator);
        $this->assertEquals(0, $number);
        $this->assertEquals(1, $sequenceGenerator->getNextNumberFor($sequenceGenerator));

        $pids = [];
        for ($i = 0; $i < 10; $i++) {
            $pid = pcntl_fork();
            if ($pid) {
                $pids[] = $pid;
            } else {
                for ($j = 0; $j < 10; $j++) {
                    $sequenceGenerator->getNextNumberFor($sequenceGenerator);
                }
                //making a hard exit to avoid phpunit having the tables cleaned up again
                exit;
            }
        }
        foreach ($pids as $pid) {
            $status = 0;
            pcntl_waitpid($pid, $status);
        }
        $this->assertEquals(101, $sequenceGenerator->getLastNumberFor($sequenceGenerator));
    }

    /**
     * @test
     */
    public function advanceTest()
    {
        $sequenceGenerator = $this->objectManager->get(SequenceGenerator::class);

        $sequenceGenerator->advanceTo(100, $sequenceGenerator);
        $this->assertEquals(100, $sequenceGenerator->getLastNumberFor($sequenceGenerator));
        $this->assertEquals(0, $sequenceGenerator->getLastNumberFor('strangeOtherSequence'));
    }
}
