<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Reducer;

use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Entity\Bug\Steps;
use Tienvx\Bundle\MbtBundle\Exception\RuntimeException;
use Tienvx\Bundle\MbtBundle\Message\ReduceBugMessage;
use Tienvx\Bundle\MbtBundle\Model\Bug\StepInterface;
use Tienvx\Bundle\MbtBundle\Model\Bug\StepsInterface;
use Tienvx\Bundle\MbtBundle\Model\BugInterface;
use Tienvx\Bundle\MbtBundle\Reducer\HandlerInterface;
use Tienvx\Bundle\MbtBundle\Service\StepsBuilderInterface;
use Tienvx\Bundle\MbtBundle\Service\StepsRunnerInterface;

class HandlerTestCase extends TestCase
{
    protected HandlerInterface $handler;
    protected EntityManagerInterface $entityManager;
    protected MessageBusInterface $messageBus;
    protected StepsRunnerInterface $stepsRunner;
    protected StepsBuilderInterface $stepsBuilder;
    protected StepsInterface $newSteps;
    protected BugInterface $bug;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->messageBus = $this->createMock(MessageBusInterface::class);
        $this->stepsRunner = $this->createMock(StepsRunnerInterface::class);
        $this->stepsBuilder = $this->createMock(StepsBuilderInterface::class);
        $this->newSteps = new Steps();
        $this->newSteps->setSteps(array_fill(0, 4, $this->createMock(StepInterface::class)));
        $this->bug = new Bug();
        $this->bug->setId(1);
        $this->bug->setMessage('Something wrong');
        $this->stepsBuilder->expects($this->once())->method('create')->with($this->bug, 1, 2)->willReturn($this->newSteps);
    }

    public function testHandleOldBug(): void
    {
        $steps = new Steps();
        $steps->setSteps(array_fill(0, 3, $this->createMock(StepInterface::class)));
        $this->bug->setSteps($steps);
        $this->stepsRunner->expects($this->never())->method('run');
        $this->handler->handle($this->bug, 1, 2);
    }

    public function testRun(): void
    {
        $steps = new Steps();
        $steps->setSteps(array_fill(0, 5, $this->createMock(StepInterface::class)));
        $this->bug->setSteps($steps);
        $this->stepsRunner->expects($this->once())->method('run')->with($this->newSteps->getSteps())->willReturnCallback(
            function (): iterable {
                foreach ($this->newSteps->getSteps() as $step) {
                    yield $step;
                }
            }
        );
        $this->handler->handle($this->bug, 1, 2);
    }

    public function testRunIntoException(): void
    {
        $steps = new Steps();
        $steps->setSteps(array_fill(0, 5, $this->createMock(StepInterface::class)));
        $this->bug->setSteps($steps);
        $this->stepsRunner->expects($this->once())->method('run')->with($this->newSteps->getSteps())->willThrowException(new RuntimeException('Something else wrong'));
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Something else wrong');
        $this->handler->handle($this->bug, 1, 2);
    }

    public function testRunFoundSameBug(): void
    {
        $steps = new Steps();
        $steps->setSteps(array_fill(0, 5, $this->createMock(StepInterface::class)));
        $this->bug->setSteps($steps);
        $this->entityManager->expects($this->once())->method('refresh')->with($this->bug);
        $this->entityManager->expects($this->once())->method('lock')->with($this->bug, LockMode::PESSIMISTIC_WRITE);
        $this->entityManager->expects($this->once())->method('transactional')->with($this->callback(function ($callback) {
            $callback();

            return true;
        }));
        $this->messageBus->expects($this->once())->method('dispatch')->with($this->isInstanceOf(ReduceBugMessage::class))->willReturn(new Envelope(new \stdClass()));
        $this->stepsRunner->expects($this->once())->method('run')->with($this->newSteps->getSteps())->willThrowException(new Exception('Something wrong'));
        $this->handler->handle($this->bug, 1, 2);
        $this->assertSame($this->newSteps, $this->bug->getSteps());
    }
}