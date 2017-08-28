<?php
namespace DigiComp\Sequence\Domain\Model;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * SequenceInsert
 *
 * @author fcool
 * @Flow\Scope("prototype")
 * @Flow\Entity
 * @ORM\Table(indexes={@ORM\Index(name="type_idx", columns={"type"})})
 */
class Insert
{
    /**
     * @var int
     * @Flow\Identity
     * @ORM\Id
     */
    protected $number;

    /**
     * @var string
     * @Flow\Identity
     * @ORM\Id
     */
    protected $type;

    /**
     * @param int $number
     * @param string|object $type
     */
    public function __construct($number, $type)
    {
        $this->setNumber($number);
        $this->setType($type);
    }

    /**
     * @return int
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @param int $number
     */
    public function setNumber($number)
    {
        $this->number = $number;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string|object $type
     */
    public function setType($type)
    {
        if (is_object($type)) {
            $type = get_class($type);
        }
        $this->type = $type;
    }
}
