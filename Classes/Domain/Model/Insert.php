<?php

declare(strict_types=1);

namespace DigiComp\Sequence\Domain\Model;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * SequenceInsert
 *
 * @Flow\Entity
 * @ORM\Table(indexes={
 *     @ORM\Index(name="type_idx", columns={"type"})
 * })
 */
class Insert
{
    /**
     * @Flow\Identity
     * @ORM\Id
     * @var int
     */
    protected int $number;

    /**
     * @Flow\Identity
     * @ORM\Id
     * @var string
     */
    protected string $type;

    /**
     * @param int $number
     * @param string|object $type
     */
    public function __construct(int $number, $type)
    {
        $this->setNumber($number);
        $this->setType($type);
    }

    /**
     * @return int
     */
    public function getNumber(): int
    {
        return $this->number;
    }

    /**
     * @param int $number
     */
    public function setNumber(int $number): void
    {
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
     * @param string|object $type
     */
    public function setType($type): void
    {
        if (\is_object($type)) {
            $type = \get_class($type);
        }
        $this->type = $type;
    }
}
