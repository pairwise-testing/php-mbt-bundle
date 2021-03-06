<?php

namespace Tienvx\Bundle\MbtBundle\Message;

class ReportBugMessage implements MessageInterface
{
    protected int $bugId;

    public function __construct(int $bugId)
    {
        $this->bugId = $bugId;
    }

    public function getBugId(): int
    {
        return $this->bugId;
    }
}
