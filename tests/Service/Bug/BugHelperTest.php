<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Service\Bug;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Entity\Model\Revision;
use Tienvx\Bundle\MbtBundle\Entity\Progress;
use Tienvx\Bundle\MbtBundle\Entity\Task;
use Tienvx\Bundle\MbtBundle\Entity\Task\SeleniumConfig;
use Tienvx\Bundle\MbtBundle\Exception\RuntimeException;
use Tienvx\Bundle\MbtBundle\Exception\UnexpectedValueException;
use Tienvx\Bundle\MbtBundle\Message\RecordVideoMessage;
use Tienvx\Bundle\MbtBundle\Message\ReportBugMessage;
use Tienvx\Bundle\MbtBundle\Model\Bug\StepInterface;
use Tienvx\Bundle\MbtBundle\Model\BugInterface;
use Tienvx\Bundle\MbtBundle\Model\Model\RevisionInterface;
use Tienvx\Bundle\MbtBundle\Model\ProgressInterface;
use Tienvx\Bundle\MbtBundle\Model\TaskInterface;
use Tienvx\Bundle\MbtBundle\Provider\ProviderManager;
use Tienvx\Bundle\MbtBundle\Reducer\ReducerInterface;
use Tienvx\Bundle\MbtBundle\Reducer\ReducerManagerInterface;
use Tienvx\Bundle\MbtBundle\Service\Bug\BugHelper;
use Tienvx\Bundle\MbtBundle\Service\Bug\BugHelperInterface;
use Tienvx\Bundle\MbtBundle\Service\Bug\BugNotifierInterface;
use Tienvx\Bundle\MbtBundle\Service\Bug\BugProgressInterface;
use Tienvx\Bundle\MbtBundle\Service\StepRunnerInterface;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Service\Bug\BugHelper
 * @covers \Tienvx\Bundle\MbtBundle\Model\Bug\Step
 * @covers \Tienvx\Bundle\MbtBundle\Entity\Bug
 * @covers \Tienvx\Bundle\MbtBundle\Entity\Task
 * @covers \Tienvx\Bundle\MbtBundle\Model\Bug
 * @covers \Tienvx\Bundle\MbtBundle\Model\Task
 * @covers \Tienvx\Bundle\MbtBundle\Model\Progress
 * @covers \Tienvx\Bundle\MbtBundle\Model\Task\TaskConfig
 * @covers \Tienvx\Bundle\MbtBundle\Model\Task\SeleniumConfig
 * @covers \Tienvx\Bundle\MbtBundle\Message\ReportBugMessage
 * @covers \Tienvx\Bundle\MbtBundle\Message\RecordVideoMessage
 */
class BugHelperTest extends TestCase
{
    protected ReducerManagerInterface $reducerManager;
    protected EntityManagerInterface $entityManager;
    protected MessageBusInterface $messageBus;
    protected BugProgressInterface $bugProgress;
    protected BugNotifierInterface $notifyHelper;
    protected ProviderManager $providerManager;
    protected StepRunnerInterface $stepRunner;
    protected BugHelperInterface $helper;
    protected Connection $connection;
    protected TaskInterface $task;
    protected BugInterface $bug;
    protected ProgressInterface $progress;
    protected RevisionInterface $revision;

    protected function setUp(): void
    {
        $this->reducerManager = $this->createMock(ReducerManagerInterface::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->messageBus = $this->createMock(MessageBusInterface::class);
        $this->bugProgress = $this->createMock(BugProgressInterface::class);
        $this->notifyHelper = $this->createMock(BugNotifierInterface::class);
        $this->providerManager = $this->createMock(ProviderManager::class);
        $this->stepRunner = $this->createMock(StepRunnerInterface::class);
        $this->helper = new BugHelper(
            $this->reducerManager,
            $this->entityManager,
            $this->messageBus,
            $this->bugProgress,
            $this->notifyHelper,
            $this->providerManager,
            $this->stepRunner
        );
        $this->connection = $this->createMock(Connection::class);
        $this->revision = new Revision();
        $this->task = new Task();
        $this->task->setModelRevision($this->revision);
        $this->task->getTaskConfig()->setReducer('random');
        $seleniumConfig = new SeleniumConfig();
        $seleniumConfig->setProvider('current-provider');
        $this->task->setSeleniumConfig($seleniumConfig);
        $this->progress = new Progress();
        $this->progress->setTotal(10);
        $this->progress->setProcessed(10);
        $this->bug = new Bug();
        $this->bug->setProgress($this->progress);
        $this->bug->setId(123);
        $this->bug->setTask($this->task);
        $this->bug->setSteps([
            $this->createMock(StepInterface::class),
            $this->createMock(StepInterface::class),
            $this->createMock(StepInterface::class),
        ]);
        $this->bug->setReducing(false);
    }

    public function testCreateBug(): void
    {
        $steps = [
            $this->createMock(StepInterface::class),
            $this->createMock(StepInterface::class),
            $this->createMock(StepInterface::class),
        ];
        $bug = $this->helper->createBug($steps, 'Something wrong');
        $this->assertInstanceOf(BugInterface::class, $bug);
        $this->assertSame($steps, $bug->getSteps());
        $this->assertSame('', $bug->getTitle());
        $this->assertSame('Something wrong', $bug->getMessage());
    }

    public function testReduceMissingBug(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Can not reduce bug 123: bug not found');
        $this->entityManager->expects($this->once())->method('find')->with(Bug::class, 123)->willReturn(null);
        $this->helper->reduceBug(123);
    }

    public function testNotFinishReduceBug(): void
    {
        $reducer = $this->createMock(ReducerInterface::class);
        $reducer->expects($this->once())->method('dispatch')->with($this->bug)->willReturn(5);
        $this->reducerManager->expects($this->once())->method('getReducer')->with('random')->willReturn($reducer);
        $this->messageBus->expects($this->never())->method('dispatch');
        $this->entityManager->expects($this->once())->method('find')->with(Bug::class, 123)->willReturn($this->bug);
        $this->entityManager->expects($this->once())->method('flush');
        $this->bugProgress->expects($this->once())->method('increaseTotal')->with($this->bug, 5);
        $this->helper->reduceBug(123);
        $this->assertTrue($this->bug->isReducing());
    }

    public function testFinishReduceBug(): void
    {
        $reducer = $this->createMock(ReducerInterface::class);
        $reducer->expects($this->once())->method('dispatch')->with($this->bug)->willReturn(0);
        $this->reducerManager->expects($this->once())->method('getReducer')->with('random')->willReturn($reducer);
        $this->messageBus
            ->expects($this->exactly(2))
            ->method('dispatch')
            ->with($this->callback(function ($message) {
                return (
                        $message instanceof RecordVideoMessage
                        || $message instanceof ReportBugMessage
                    )
                    && 123 === $message->getBugId();
            }))
            ->willReturn(new Envelope(new \stdClass()));
        $this->entityManager->expects($this->once())->method('find')->with(Bug::class, 123)->willReturn($this->bug);
        $this->entityManager->expects($this->exactly(2))->method('flush');
        $this->connection->expects($this->once())->method('connect');
        $this->entityManager->expects($this->once())->method('getConnection')->willReturn($this->connection);
        $this->bugProgress->expects($this->never())->method('increaseTotal');
        $this->helper->reduceBug(123);
        $this->assertFalse($this->bug->isReducing());
    }

    public function testReportMissingBug(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Can not report bug 123: bug not found');
        $this->entityManager->expects($this->once())->method('find')->with(Bug::class, 123)->willReturn(null);
        $this->helper->reportBug(123);
    }

    public function testReportBug(): void
    {
        $task = new Task();
        $task->setAuthor(22);
        $task->getTaskConfig()->setNotifyAuthor(true);
        $task->getTaskConfig()->setNotifyChannels(['email', 'chat/slack', 'sms/nexmo']);
        $bug = new Bug();
        $bug->setTitle('New bug found');
        $bug->setId(123);
        $bug->setMessage('Something wrong');
        $bug->setTask($task);
        $this->entityManager->expects($this->once())->method('find')->with(Bug::class, 123)->willReturn($bug);
        $this->notifyHelper
            ->expects($this->once())
            ->method('notify')
            ->with($bug);
        $this->helper->reportBug(123);
    }

    public function testReduceStepsMissingBug(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Can not reduce steps for bug 123: bug not found');
        $this->entityManager->expects($this->once())->method('find')->with(Bug::class, 123)->willReturn(null);
        $this->helper->reduceSteps(123, 6, 1, 2);
    }

    public function testReduceReducedSteps(): void
    {
        $this->reducerManager->expects($this->never())->method('getReducer');
        $this->entityManager->expects($this->once())->method('find')->with(Bug::class, 123)->willReturn($this->bug);
        $this->helper->reduceSteps(123, 4, 1, 2);
    }

    public function testNotStopReduceSteps(): void
    {
        $this->progress->setProcessed(5);
        $this->bug->setSteps([
            $this->createMock(StepInterface::class),
            $this->createMock(StepInterface::class),
            $this->createMock(StepInterface::class),
            $this->createMock(StepInterface::class),
        ]);
        $reducer = $this->createMock(ReducerInterface::class);
        $reducer->expects($this->once())->method('handle')->with($this->bug, 1, 2);
        $this->reducerManager->expects($this->once())->method('getReducer')->with('random')->willReturn($reducer);
        $this->messageBus->expects($this->never())->method('dispatch');
        $this->entityManager->expects($this->once())->method('find')->with(Bug::class, 123)->willReturn($this->bug);
        $this->bugProgress
            ->expects($this->once())
            ->method('increaseProcessed')
            ->with($this->bug, 1)
            ->willReturnCallback(fn () => $this->bug->setReducing(true));
        $this->helper->reduceSteps(123, 4, 1, 2);
    }

    public function testStopReduceStepsAndRecordVideo(): void
    {
        $this->task->getTaskConfig()->setNotifyChannels([]);
        $this->bug->setSteps([
            $this->createMock(StepInterface::class),
            $this->createMock(StepInterface::class),
            $this->createMock(StepInterface::class),
            $this->createMock(StepInterface::class),
        ]);
        $reducer = $this->createMock(ReducerInterface::class);
        $reducer->expects($this->once())->method('handle')->with($this->bug, 1, 2);
        $this->reducerManager->expects($this->once())->method('getReducer')->with('random')->willReturn($reducer);
        $this->messageBus
            ->expects($this->exactly(1))
            ->method('dispatch')
            ->with($this->callback(
                fn ($message) => $message instanceof RecordVideoMessage && 123 === $message->getBugId()
            ))
            ->willReturn(new Envelope(new \stdClass()));
        $this->entityManager->expects($this->once())->method('find')->with(Bug::class, 123)->willReturn($this->bug);
        $this->bugProgress->expects($this->once())->method('increaseProcessed')->with($this->bug, 1);
        $this->helper->reduceSteps(123, 4, 1, 2);
    }

    public function testStopReduceStepsAndReportBug(): void
    {
        $this->task->getTaskConfig()->setNotifyChannels(['email', 'chat/slack']);
        $this->bug->setSteps([
            $this->createMock(StepInterface::class),
            $this->createMock(StepInterface::class),
            $this->createMock(StepInterface::class),
            $this->createMock(StepInterface::class),
        ]);
        $reducer = $this->createMock(ReducerInterface::class);
        $reducer->expects($this->once())->method('handle')->with($this->bug, 1, 2);
        $this->reducerManager->expects($this->once())->method('getReducer')->with('random')->willReturn($reducer);
        $this->messageBus
            ->expects($this->exactly(2))
            ->method('dispatch')
            ->withConsecutive(
                [
                    $this->callback(
                        fn ($message) => $message instanceof RecordVideoMessage && 123 === $message->getBugId()
                    ),
                ],
                [
                    $this->callback(
                        fn ($message) => $message instanceof ReportBugMessage && 123 === $message->getBugId()
                    ),
                ]
            )
            ->willReturn(new Envelope(new \stdClass()));
        $this->entityManager->expects($this->once())->method('find')->with(Bug::class, 123)->willReturn($this->bug);
        $this->bugProgress->expects($this->once())->method('increaseProcessed')->with($this->bug, 1);
        $this->helper->reduceSteps(123, 4, 1, 2);
    }

    public function testRecordVideoMissingBug(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Can not record video for bug 123: bug not found');
        $this->entityManager->expects($this->once())->method('find')->with(Bug::class, 123)->willReturn(null);
        $this->helper->recordVideo(123);
    }

    public function testRecordVideoThrowException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Exception that we care about');
        $driver = $this->createMock(RemoteWebDriver::class);
        $driver->expects($this->once())->method('quit');
        $this->providerManager
            ->expects($this->once())
            ->method('createDriver')
            ->with($this->task, 123)
            ->willReturn($driver);
        $this->stepRunner
            ->expects($this->once())
            ->method('run')
            ->with($this->isInstanceOf(StepInterface::class), $this->revision, $driver)
            ->willThrowException(new RuntimeException('Exception that we care about'));
        $this->entityManager->expects($this->once())->method('find')->with(Bug::class, 123)->willReturn($this->bug);
        $this->helper->recordVideo(123);
    }

    public function testRecordVideoNotThrowException(): void
    {
        $driver = $this->createMock(RemoteWebDriver::class);
        $driver->expects($this->once())->method('quit');
        $this->providerManager
            ->expects($this->once())
            ->method('createDriver')
            ->with($this->task, 123)
            ->willReturn($driver);
        $this->stepRunner
            ->expects($this->exactly(2))
            ->method('run')
            ->with($this->isInstanceOf(StepInterface::class), $this->revision, $driver)
            ->willReturnOnConsecutiveCalls(
                null,
                $this->throwException(new Exception("Exception that we don't care about")),
            );
        $this->entityManager->expects($this->once())->method('find')->with(Bug::class, 123)->willReturn($this->bug);
        $this->helper->recordVideo(123);
    }

    public function testRecordVideo(): void
    {
        $driver = $this->createMock(RemoteWebDriver::class);
        $driver->expects($this->once())->method('quit');
        $this->providerManager
            ->expects($this->once())
            ->method('createDriver')
            ->with($this->task, 123)
            ->willReturn($driver);
        $this->stepRunner
            ->expects($this->exactly(3))
            ->method('run')
            ->with($this->isInstanceOf(StepInterface::class), $this->revision, $driver);
        $this->entityManager->expects($this->once())->method('find')->with(Bug::class, 123)->willReturn($this->bug);
        $this->helper->recordVideo(123);
    }
}
