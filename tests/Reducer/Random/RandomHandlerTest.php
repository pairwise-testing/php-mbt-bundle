<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Reducer\Random;

use Tienvx\Bundle\MbtBundle\Reducer\Random\RandomHandler;
use Tienvx\Bundle\MbtBundle\Tests\Reducer\HandlerTestCase;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Reducer\Random\RandomHandler
 * @covers \Tienvx\Bundle\MbtBundle\Reducer\HandlerTemplate
 * @covers \Tienvx\Bundle\MbtBundle\Entity\Bug
 * @covers \Tienvx\Bundle\MbtBundle\Model\Bug
 * @covers \Tienvx\Bundle\MbtBundle\Message\ReduceBugMessage
 */
class RandomHandlerTest extends HandlerTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->handler = new RandomHandler($this->entityManager, $this->messageBus, $this->stepsRunner, $this->stepsBuilder);
    }
}
