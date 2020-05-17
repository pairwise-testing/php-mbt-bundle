<?php

namespace Tienvx\Bundle\MbtBundle\Message;

class TestPredefinedCaseMessage implements MessageInterface
{
    /**
     * @var string
     */
    protected $predefinedCase;

    public function __construct(string $predefinedCase)
    {
        $this->predefinedCase = $predefinedCase;
    }

    public function getPredefinedCase(): string
    {
        return $this->predefinedCase;
    }
}
