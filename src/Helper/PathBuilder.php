<?php

namespace Tienvx\Bundle\MbtBundle\Helper;

use Exception;
use Fhaculty\Graph\Edge\Directed;
use Fhaculty\Graph\Graph;
use Graphp\Algorithms\ShortestPath\Dijkstra;
use Tienvx\Bundle\MbtBundle\Graph\Path;

class PathBuilder
{
    /**
     * @param string $path
     * @return Path
     * @throws Exception
     */
    public static function build(string $path): Path
    {
        $path = unserialize($path);

        if (!$path instanceof Path) {
            throw new Exception(sprintf('Path must be instance of %s', Path::class));
        }

        return $path;
    }

    /**
     * @param Graph $graph
     * @param Path $path
     * @param int $from
     * @param int $to
     * @return Path
     * @throws Exception
     */
    public static function createWithShortestPath(Graph $graph, Path $path, int $from, int $to): Path
    {
        $fromPlaces = $path->getPlaces($from);
        $toPlaces = $path->getPlaces($to);
        if (array_diff($fromPlaces, $toPlaces)) {
            if (count($fromPlaces) === 1 && count($toPlaces) === 1) {
                // Get shortest path between 2 vertices by algorithm.
                $fromVertex = $graph->getVertex($fromPlaces[0]);
                $toVertex = $graph->getVertex($toPlaces[0]);
                $algorithm = new Dijkstra($fromVertex);
                $edges = $algorithm->getEdgesTo($toVertex);
                $middleTransitions = [$edges[0]->get];
                $middleData = array_fill(0, count($edges), null);
                $middlePlaces = [null];
                foreach ($edges as $index => $edge) {
                    if ($edge instanceof Directed) {
                        if ($index === 0) {
                            $middlePlaces[] = $edge->getVertexStart()->getId();
                        }
                        $middleTransitions[] = $edge->getAttribute('name');
                        $middlePlaces[] = $edge->getVertexEnd()->getId();
                    } else {
                        throw new Exception('Only support directed graph');
                    }
                }
                return static::create($path, $from, $to, $middleTransitions, $middleData, $middlePlaces);
            } else {
                throw new Exception('Can not create new path with shortest path');
            }
        } else {
            return static::create($path, $from, $to, [], [], []);
        }
    }

    /**
     * @param Path $path
     * @param int $from
     * @param int $to
     * @return Path
     * @throws Exception
     */
    public static function createWithoutLoop(Path $path, int $from, int $to): Path
    {
        $fromPlaces = $path->getPlaces($from);
        $toPlaces = $path->getPlaces($to);
        if (!array_diff($fromPlaces, $toPlaces)) {
            return static::create($path, $from, $to, [], [], []);
        } else {
            throw new Exception('Can not create new path without loop');
        }
    }

    public static function create(Path $path, int $from, int $to, array $middleTransitions, array $middleData, array $middlePlaces): Path
    {
        $beginTransitions = [];
        $endTransitions = [];
        $beginData = [];
        $endData = [];
        $beginPlaces = [];
        $endPlaces = [];
        foreach ($path as $index => $step) {
            if ($index < $from) {
                $beginTransitions[] = $step[0];
                $beginData[] = $step[1];
                $beginPlaces[] = $step[2];
            } elseif ($index >= $to) {
                $endTransitions[] = $step[0];
                $endData[] = $step[1];
                $endPlaces[] = $step[2];
            }
        }

        $transitions = array_merge($beginTransitions, $middleTransitions, $endTransitions);
        $data = array_merge($beginData, $middleData, $endData);
        $places = array_merge($beginPlaces, $middlePlaces, $endPlaces);
        $newPath = new Path($transitions, $data, $places);
        return $newPath;
    }
}
