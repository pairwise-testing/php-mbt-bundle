<?php

namespace Tienvx\Bundle\MbtBundle\Generator;

use Exception;
use Generator;
use Graphp\Algorithms\TravelingSalesmanProblem\Bruteforce;
use Symfony\Component\Workflow\StateMachine;
use Symfony\Component\Workflow\Workflow;
use Tienvx\Bundle\MbtBundle\Helper\GraphBuilder;
use Tienvx\Bundle\MbtBundle\Subject\Subject;

class AllPlacesGenerator extends AbstractGenerator
{
    /**
     * @param Workflow $workflow
     * @param Subject $subject
     * @return Generator
     * @throws Exception
     */
    public function getAvailableTransitions(Workflow $workflow, Subject $subject): Generator
    {
        if (!$workflow instanceof StateMachine) {
            throw new Exception(sprintf('Generator %s only support model type state machine', static::getName()));
        }

        $graph = GraphBuilder::build($workflow);
        $algorithm = new Bruteforce($graph);
        $edges = $algorithm->getEdges();
        $edges = $edges->getVector();
        while (!empty($edges)) {
            $edge = array_shift($edges);
            $transitionName = $edge->getAttribute('name');
            if ($workflow->can($subject, $transitionName)) {
                yield $transitionName;
            } else {
                break;
            }
        }
    }

    public static function getName()
    {
        return 'all-places';
    }
}
