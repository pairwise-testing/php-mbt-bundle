<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Message;

use Exception;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Entity\Generator;
use Tienvx\Bundle\MbtBundle\Entity\GeneratorOptions;
use Tienvx\Bundle\MbtBundle\Entity\Model;
use Tienvx\Bundle\MbtBundle\Entity\Reducer;
use Tienvx\Bundle\MbtBundle\Entity\Task;
use Tienvx\Bundle\MbtBundle\Entity\Path;

class TestBugTest extends MessageTestCase
{
    /**
     * @param string $model
     * @param string $generator
     * @param string $reducer
     * @param bool   $regression
     *
     * @throws Exception
     * @dataProvider consumeMessageData
     */
    public function testExecute(string $model, string $generator, string $reducer, bool $regression)
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = self::$container->get(EntityManagerInterface::class);

        if ($regression) {
            $bugMessage = 'You added an out-of-stock product into cart! Can not checkout';
            $path = new Path([
                [null, null, ['home']],
                ['viewAnyCategoryFromHome', ['category' => '57'], ['category']],
                ['addFromCategory', ['product' => '49'], ['category']],
                ['checkoutFromCategory', [], ['checkout']],
            ]);
        } else {
            $bugMessage = 'Fixed bug';
            $path = new Path([
                [null, null, ['home']],
                ['viewProductFromHome', ['product' => '40'], ['product']],
                ['addFromProduct', [], ['product']],
                ['viewCartFromProduct', [], ['cart']],
                ['useCoupon', [], ['cart']],
            ]);
        }

        $task = new Task();
        $task->setTitle('Just dummy task');
        $task->setModel(new Model($model));
        $task->setGenerator(new Generator('random'));
        $task->setReducer(new Reducer('loop'));
        $entityManager->persist($task);

        $bug = new Bug();
        $bug->setTitle('Test regression bug');
        $bug->setPath($path);
        $bug->setLength($path->countPlaces());
        $bug->setTask($task);
        $bug->setBugMessage($bugMessage);
        $entityManager->persist($bug);

        $entityManager->flush();

        $this->clearMessages();
        $this->clearReport();
        $this->removeScreenshots();

        $generatorOptions = new GeneratorOptions();
        $generatorOptions->setBugId($bug->getId());

        $task = new Task();
        $task->setTitle('Test regression task');
        $task->setModel(new Model($model));
        $task->setGenerator(new Generator($generator));
        $task->setGeneratorOptions($generatorOptions);
        $task->setReducer(new Reducer($reducer));
        $task->setTakeScreenshots(false);
        $entityManager->persist($task);
        $entityManager->flush();

        $this->consumeMessages();

        /** @var EntityRepository $entityRepository */
        $entityRepository = $entityManager->getRepository(Bug::class);
        /** @var Bug[] $bugs */
        $bugs = $entityRepository->findAll();

        $this->assertEquals($regression ? 2 : 1, count($bugs));
        $this->assertEquals('completed', $task->getStatus());
    }

    public function consumeMessageData()
    {
        return [
            ['shopping_cart', 'test-bug', 'loop', true],
            ['shopping_cart', 'test-bug', 'loop', false],
        ];
    }
}