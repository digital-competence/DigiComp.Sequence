<?php

declare(strict_types=1);

namespace DigiComp\Sequence\Domain\Model;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * This class is only here to set up the table. We never create an instance of this class.
 *
 * @Flow\Entity
 */
class SequenceEntry
{
    /**
     * @ORM\Id
     * @var string
     */
    protected string $type;

    /**
     * @ORM\Id
     * @var int
     */
    protected int $number;
}
