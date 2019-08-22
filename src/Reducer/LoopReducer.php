<?php

namespace Tienvx\Bundle\MbtBundle\Reducer;

use Exception;
use Symfony\Component\Workflow\Workflow;
use Throwable;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Helper\StepsBuilder;
use Tienvx\Bundle\MbtBundle\Helper\StepsRunner;
use Tienvx\Bundle\MbtBundle\Message\FinishReduceStepsMessage;
use Tienvx\Bundle\MbtBundle\Message\ReduceStepsMessage;

class LoopReducer extends AbstractReducer
{
    /**
     * @param Bug      $bug
     * @param Workflow $workflow
     * @param int      $length
     * @param int      $from
     * @param int      $to
     *
     * @throws Exception
     * @throws Throwable
     */
    public function handle(Bug $bug, Workflow $workflow, int $length, int $from, int $to)
    {
        $steps = $bug->getSteps();
        $model = $bug->getTask()->getModel()->getName();

        if ($steps->getLength() === $length) {
            // The reproduce path has not been reduced.
            if ($from < $steps->getLength() && $to < $steps->getLength() && !array_diff($steps->getPlacesAt($from), $steps->getPlacesAt($to))) {
                $newSteps = StepsBuilder::createWithoutLoop($steps, $from, $to);
                // Make sure new path shorter than old path.
                if ($newSteps->getLength() < $steps->getLength()) {
                    try {
                        $subject = $this->subjectManager->createSubject($model);
                        StepsRunner::run($newSteps, $workflow, $subject);
                    } catch (Throwable $newThrowable) {
                        if ($newThrowable->getMessage() === $bug->getBugMessage()) {
                            $this->updateSteps($bug, $newSteps);
                        }
                    }
                }
            }
        }

        $this->messageBus->dispatch(new FinishReduceStepsMessage($bug->getId()));
    }

    /**
     * @param Bug $bug
     *
     * @return int
     *
     * @throws Exception
     */
    public function dispatch(Bug $bug): int
    {
        $steps = $bug->getSteps();
        $messagesCount = 0;

        $distance = $steps->getLength();
        while ($distance > 0) {
            for ($i = 0; $i < $steps->getLength(); ++$i) {
                $j = $i + $distance;
                if ($j < $steps->getLength() && !array_diff($steps->getPlacesAt($i), $steps->getPlacesAt($j))) {
                    $message = new ReduceStepsMessage($bug->getId(), static::getName(), $steps->getLength(), $i, $j);
                    $this->messageBus->dispatch($message);
                    ++$messagesCount;
                    if ($messagesCount >= $steps->getLength()) {
                        // Prevent too many messages.
                        break 2;
                    }
                }
            }
            --$distance;
        }

        return $messagesCount;
    }

    public static function getName(): string
    {
        return 'loop';
    }

    public function getLabel(): string
    {
        return 'Loop';
    }
}