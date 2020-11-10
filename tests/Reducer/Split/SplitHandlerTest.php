<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Reducer\Split;

use Tienvx\Bundle\MbtBundle\Reducer\Split\SplitHandler;
use Tienvx\Bundle\MbtBundle\Tests\Reducer\HandlerTestCase;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Reducer\Split\SplitHandler
 * @covers \Tienvx\Bundle\MbtBundle\Reducer\HandlerTemplate
 * @covers \Tienvx\Bundle\MbtBundle\Entity\Bug
 * @covers \Tienvx\Bundle\MbtBundle\Model\Bug
 * @covers \Tienvx\Bundle\MbtBundle\Model\Bug\Steps
 * @covers \Tienvx\Bundle\MbtBundle\Message\ReduceBugMessage
 */
class SplitHandlerTest extends HandlerTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->handler = new SplitHandler($this->entityManager, $this->messageBus, $this->stepsRunner, $this->stepsBuilder);
    }
}