<?php

namespace Tienvx\Bundle\MbtBundle\PathReducer;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Workflow\Registry;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Event\ReducerFinishEvent;
use Tienvx\Bundle\MbtBundle\Subject\SubjectManager;

abstract class AbstractPathReducer implements PathReducerInterface
{
    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * @var Registry
     */
    protected $workflowRegistry;

    /**
     * @var SubjectManager
     */
    protected $subjectManager;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    public function __construct(
        EventDispatcherInterface $dispatcher,
        Registry $workflowRegistry,
        SubjectManager $subjectManager,
        EntityManagerInterface $entityManager)
    {
        $this->dispatcher       = $dispatcher;
        $this->workflowRegistry = $workflowRegistry;
        $this->subjectManager   = $subjectManager;
        $this->entityManager    = $entityManager;
    }

    protected function finish(int $bugId)
    {
        $event = new ReducerFinishEvent($bugId);

        $this->dispatcher->dispatch('tienvx_mbt.finish_reduce', $event);
    }

    /**
     * @param Bug $bug
     * @param string $path
     * @param int $length
     * @throws \Exception
     */
    protected function updatePath(Bug $bug, string $path, int $length)
    {
        $bug->setPath($path);
        $bug->setLength($length);
        $this->entityManager->flush();
    }

    public function handle(string $message)
    {
    }
}
