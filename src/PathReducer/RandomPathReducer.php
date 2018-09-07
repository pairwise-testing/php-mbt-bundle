<?php

namespace Tienvx\Bundle\MbtBundle\PathReducer;

use Exception;
use Symfony\Component\Workflow\StateMachine;
use Throwable;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Helper\GraphBuilder;
use Tienvx\Bundle\MbtBundle\Helper\PathBuilder;
use Tienvx\Bundle\MbtBundle\Helper\Randomizer;
use Tienvx\Bundle\MbtBundle\Helper\PathRunner;

class RandomPathReducer extends AbstractPathReducer
{
    /**
     * @param Bug $bug
     * @throws \Exception
     */
    public function reduce(Bug $bug)
    {
        $model = $bug->getTask()->getModel();
        $subject = $this->subjectManager->createSubjectForModel($model);
        $workflow = $this->workflowRegistry->get($subject, $model);

        if (!$workflow instanceof StateMachine) {
            throw new Exception(sprintf('Path reducer %s only support model type state machine', static::getName()));
        }

        $graph = GraphBuilder::build($workflow);
        $path = PathBuilder::build($bug->getPath());

        $try = 1;
        $maxTries = $path->countTransitions();

        while ($try <= $maxTries) {
            list($i, $j) = Randomizer::randomPair(0, $path->countTransitions());
            $newPath = PathBuilder::createWithShortestPath($graph, $path, $i, $j);
            // Make sure new path shorter than old path.
            if ($newPath->countTransitions() < $path->countTransitions()) {
                try {
                    $subject = $this->subjectManager->createSubjectForModel($model);
                    PathRunner::run($newPath, $workflow, $subject);
                } catch (Throwable $newThrowable) {
                    if ($newThrowable->getMessage() === $bug->getBugMessage()) {
                        $path = $newPath;
                    }
                } finally {
                    if ($newPath->countTransitions() === $path->countTransitions()) {
                        $try = 1;
                        $maxTries = $path->countTransitions();
                    }
                }
            }
            $try++;
        }

        // Can not reduce the reproduce path (any more).
        $this->updatePath($bug, $path);
        $this->finish($bug->getId());
    }

    public static function getName()
    {
        return 'random';
    }
}
