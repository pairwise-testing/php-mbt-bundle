<?php

namespace Tienvx\Bundle\MbtBundle\Message;

class ReduceStepsMessage implements MessageInterface
{
    /**
     * @var int
     */
    protected $bugId;

    /**
     * @var string
     */
    protected $reducer;

    /**
     * @var int
     */
    protected $length;

    /**
     * @var int
     */
    protected $from;

    /**
     * @var int
     */
    protected $to;

    public function __construct(int $bugId, string $reducer, int $length, int $from, int $to)
    {
        $this->bugId = $bugId;
        $this->reducer = $reducer;
        $this->length = $length;
        $this->from = $from;
        $this->to = $to;
    }

    public function getBugId(): int
    {
        return $this->bugId;
    }

    public function getReducer(): string
    {
        return $this->reducer;
    }

    public function getLength(): int
    {
        return $this->length;
    }

    public function getFrom(): int
    {
        return $this->from;
    }

    public function getTo(): int
    {
        return $this->to;
    }
}
