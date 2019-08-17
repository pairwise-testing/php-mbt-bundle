<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Command;

use Exception;
use Symfony\Component\Console\Tester\CommandTester;
use Tienvx\Bundle\MbtBundle\Entity\Steps;

class TestModelCommandTest extends CommandTestCase
{
    public function coverageData()
    {
        return [
            ['shopping_cart', 'random', 100, 100, 24, 5],
            ['shopping_cart', 'random', 60, 80, 15, 4],
            ['shopping_cart', 'random', 75, 60, 18, 3],
            ['shopping_cart', 'all-places', null, null, 0, 5],
            ['shopping_cart', 'all-transitions', null, null, 24, 0],
            ['checkout', 'random', 100, 100, 11, 12],
            ['checkout', 'random', 60, 80, 7, 10],
            ['checkout', 'random', 40, 65, 5, 8],
        ];
    }

    /**
     * @dataProvider coverageData
     *
     * @param $model
     * @param $generator
     * @param $transitionCoverage
     * @param $placeCoverage
     * @param $transitionCount
     * @param $placeCount
     *
     * @throws Exception
     */
    public function testExecute($model, $generator, $transitionCoverage, $placeCoverage, $transitionCount, $placeCount)
    {
        $name = 'mbt:model:test';
        $input = [
            'command' => $name,
            'model' => $model,
            '--generator' => $generator,
        ];
        if ('random' === $generator) {
            $input['--generator-options'] = [
                'maxSteps' => 300,
                'transitionCoverage' => $transitionCoverage,
                'placeCoverage' => $placeCoverage,
            ];
        }

        $command = $this->application->find($name);
        $commandTester = new CommandTester($command);
        $commandTester->execute($input);

        $output = $commandTester->getDisplay();
        $steps = Steps::deserialize($output);
        $this->assertInstanceOf(Steps::class, $steps);

        if ($steps instanceof Steps) {
            $uniquePlaces = $steps->countUniquePlaces();
            $uniqueTransitions = $steps->countUniqueTransitions();
            if ('all-transitions' === $generator && array_diff($steps->getPlacesAt($steps->getLength() - 1), ['home'])) {
                // Sometime, we can't get the path through all transitions, so ignore it.
            } elseif ('all-places' === $generator && 1 === $uniqueTransitions) {
                // Sometime, we can't get the path through all places, so ignore it.
            } elseif ('random' === $generator && 300 === $steps->getLength()) {
                // Sometime we reach the path length limit, so ignore it.
                $this->assertGreaterThanOrEqual(1, $uniqueTransitions);
                $this->assertGreaterThanOrEqual(1, $uniquePlaces);
            } else {
                $this->assertGreaterThanOrEqual($transitionCount, $uniqueTransitions);
                $this->assertGreaterThanOrEqual($placeCount, $uniquePlaces);
            }
        }
    }
}
