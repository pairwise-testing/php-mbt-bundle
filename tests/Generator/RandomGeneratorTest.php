<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Generator;

use PHPUnit\Framework\TestCase;
use SingleColorPetrinet\Model\ColorfulFactory;
use SingleColorPetrinet\Service\GuardedTransitionService;
use Tienvx\Bundle\MbtBundle\Entity\Model\Revision;
use Tienvx\Bundle\MbtBundle\Entity\Task;
use Tienvx\Bundle\MbtBundle\Generator\GeneratorManager;
use Tienvx\Bundle\MbtBundle\Generator\RandomGenerator;
use Tienvx\Bundle\MbtBundle\Model\TaskInterface;
use Tienvx\Bundle\MbtBundle\Service\ExpressionLanguage;
use Tienvx\Bundle\MbtBundle\Service\Model\ModelHelper;
use Tienvx\Bundle\MbtBundle\Service\Petrinet\MarkingHelper;
use Tienvx\Bundle\MbtBundle\Service\Petrinet\PetrinetHelper;
use Tienvx\Bundle\MbtBundle\ValueObject\Model\Place;
use Tienvx\Bundle\MbtBundle\ValueObject\Model\Transition;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Generator\RandomGenerator
 * @covers \Tienvx\Bundle\MbtBundle\Generator\AbstractGenerator
 * @covers \Tienvx\Bundle\MbtBundle\Model\Generator\State
 * @covers \Tienvx\Bundle\MbtBundle\Model\Bug\Step
 * @covers \Tienvx\Bundle\MbtBundle\Entity\Task
 * @covers \Tienvx\Bundle\MbtBundle\Model\Task
 * @covers \Tienvx\Bundle\MbtBundle\Model\Task\TaskConfig
 * @covers \Tienvx\Bundle\MbtBundle\Entity\Model
 * @covers \Tienvx\Bundle\MbtBundle\Model\Model
 * @covers \Tienvx\Bundle\MbtBundle\Model\Model\Revision\Place
 * @covers \Tienvx\Bundle\MbtBundle\Model\Model\Revision\Transition
 * @covers \Tienvx\Bundle\MbtBundle\Model\Model\Revision
 * @covers \Tienvx\Bundle\MbtBundle\Service\ExpressionLanguage
 * @covers \Tienvx\Bundle\MbtBundle\Service\Petrinet\MarkingHelper
 * @covers \Tienvx\Bundle\MbtBundle\Service\Petrinet\PetrinetHelper
 * @covers \Tienvx\Bundle\MbtBundle\Service\Model\ModelHelper
 */
class RandomGeneratorTest extends TestCase
{
    protected TaskInterface $task;
    protected RandomGenerator $generator;

    protected function setUp(): void
    {
        $factory = new ColorfulFactory();
        $expressionLanguage = new ExpressionLanguage();
        $petrinetHelper = new PetrinetHelper($factory, $expressionLanguage);
        $markingHelper = new MarkingHelper($factory);
        $modelHelper = new ModelHelper();
        $transitionService = new GuardedTransitionService($factory);
        $revision = new Revision();
        $places = [
            $place1 = new Place(),
            $place2 = new Place(),
            $place3 = new Place(),
        ];
        $revision->setPlaces($places);
        $transitions = [
            $transition1 = new Transition(),
            $transition2 = new Transition(),
            $transition3 = new Transition(),
        ];
        $transition1->setFromPlaces([]);
        $transition1->setToPlaces([0]);
        $transition2->setFromPlaces([0]);
        $transition2->setToPlaces([1]);
        $transition3->setFromPlaces([1]);
        $transition3->setToPlaces([2]);
        $revision->setTransitions($transitions);
        $this->task = new Task();
        $this->task->setModelRevision($revision);
        $this->generator = new RandomGenerator(
            $petrinetHelper,
            $markingHelper,
            $modelHelper,
            $transitionService
        );
    }

    public function testGetManager(): void
    {
        $this->assertSame(GeneratorManager::class, RandomGenerator::getManager());
    }

    public function testGetName(): void
    {
        $this->assertSame('random', RandomGenerator::getName());
    }

    /**
     * @dataProvider configValidationProvider
     */
    public function testValidate(array $config, bool $valid)
    {
        $this->assertSame($valid, $this->generator->validate($config));
    }

    /**
     * @dataProvider configProvider
     */
    public function testGenerate(array $config, int $stepsCount): void
    {
        $this->task->getTaskConfig()->setGeneratorConfig($config);
        $this->assertCount($stepsCount, $this->generator->generate($this->task));
    }

    public function configValidationProvider(): array
    {
        return [
            [[], false],
            [[RandomGenerator::MAX_PLACE_COVERAGE => 55], false],
            [[RandomGenerator::MAX_PLACE_COVERAGE => 55.7], false],
            [[RandomGenerator::MAX_TRANSITION_COVERAGE => 66], false],
            [[RandomGenerator::MAX_TRANSITION_COVERAGE => 66.8], false],
            [[RandomGenerator::MAX_PLACE_COVERAGE => 100.1, RandomGenerator::MAX_TRANSITION_COVERAGE => 101.2], false],
            [[RandomGenerator::MAX_PLACE_COVERAGE => 55.7, RandomGenerator::MAX_TRANSITION_COVERAGE => 66.8], true],
        ];
    }

    public function configProvider(): array
    {
        return [
            [[RandomGenerator::MAX_PLACE_COVERAGE => 0, RandomGenerator::MAX_TRANSITION_COVERAGE => 0], 0],
            [[RandomGenerator::MAX_PLACE_COVERAGE => 0.1, RandomGenerator::MAX_TRANSITION_COVERAGE => 0.1], 1],
            [[RandomGenerator::MAX_PLACE_COVERAGE => 33, RandomGenerator::MAX_TRANSITION_COVERAGE => 33], 1],
            [[RandomGenerator::MAX_PLACE_COVERAGE => 34, RandomGenerator::MAX_TRANSITION_COVERAGE => 34], 2],
            [[RandomGenerator::MAX_PLACE_COVERAGE => 66.0, RandomGenerator::MAX_TRANSITION_COVERAGE => 56.0], 2],
            [[RandomGenerator::MAX_PLACE_COVERAGE => 70.0, RandomGenerator::MAX_TRANSITION_COVERAGE => 67.0], 3],
        ];
    }
}
