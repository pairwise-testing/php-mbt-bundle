<?php

namespace Tienvx\Bundle\MbtBundle\PathReducer;

use Exception;
use Tienvx\Bundle\MbtBundle\Entity\Bug;
use Tienvx\Bundle\MbtBundle\Entity\Path;
use Tienvx\Bundle\MbtBundle\Helper\Randomizer;
use Tienvx\Bundle\MbtBundle\Message\ReducePathMessage;

class RandomPathReducer extends AbstractPathReducer
{
    /**
     * @param int       $bugId
     * @param Path|null $newPath
     *
     * @return int
     *
     * @throws Exception
     */
    public function dispatch(int $bugId, Path $newPath = null): int
    {
        $callback = function () use ($bugId, $newPath) {
            $bug = $this->getBug($bugId, $newPath);

            if (!$bug || !$bug instanceof Bug) {
                return 0;
            }

            $path = $bug->getPath();
            $messagesCount = 0;

            if ($path->countPlaces() > 2) {
                $pairs = Randomizer::randomPairs($path->countPlaces(), floor(sqrt($path->countPlaces())));
                foreach ($pairs as $pair) {
                    $message = new ReducePathMessage($bug->getId(), static::getName(), $path->countPlaces(), $pair[0], $pair[1]);
                    $this->messageBus->dispatch($message);
                    ++$messagesCount;
                }
            }

            $bug->setMessagesCount($bug->getMessagesCount() + $messagesCount);

            return $messagesCount;
        };

        $messagesCount = $this->entityManager->transactional($callback);

        return true === $messagesCount ? 0 : $messagesCount;
    }

    public static function getName(): string
    {
        return 'random';
    }

    public function getLabel(): string
    {
        return 'Random';
    }
}
