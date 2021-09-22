<?php

declare(strict_types=1);

namespace DigiComp\Sequence\Domain\Model;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * @Flow\Entity
 * @ORM\Table(indexes={
 *     @ORM\Index(columns={"type"})
 * }, uniqueConstraints={
 *     @ORM\UniqueConstraint(columns={"type", "number"})
 * })
 */
class SequenceEntry
{
    /**
     * @var string
     */
    protected string $type;

    /**
     * @var int
     */
    protected int $number;

    /**
     * @param string $type
     * @param int $number
     */
    public function __construct(string $type, int $number)
    {
        $this->type = $type;
        $this->number = $number;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return int
     */
    public function getNumber(): int
    {
        return $this->number;
    }
}
