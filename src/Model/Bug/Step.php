<?php

namespace Tienvx\Bundle\MbtBundle\Model\Bug;

use Tienvx\Bundle\MbtBundle\Model\BugInterface;

class Step implements StepInterface
{
    protected BugInterface $bug;
    protected string $color;
    protected array $places;
    protected ?int $transition = null;

    public function __construct(array $tokensCount, string $color, ?int $transition = null)
    {
        $this->setPlaces($tokensCount);
        $this->setColor($color);
        $this->setTransition($transition);
    }

    public function setBug(BugInterface $bug): void
    {
        $this->bug = $bug;
    }

    public function getBug(): BugInterface
    {
        return $this->bug;
    }

    public function setColor(string $color): void
    {
        $this->color = $color;
    }

    public function getColor(): string
    {
        return $this->color;
    }

    public function getPlaces(): array
    {
        return $this->places;
    }

    public function setPlaces(array $places): void
    {
        $this->places = [];

        foreach ($places as $place => $tokens) {
            $this->addPlace($place, $tokens);
        }
    }

    public function addPlace(int $place, int $tokens): void
    {
        $this->places[$place] = $tokens;
    }

    public function getTransition(): ?int
    {
        return $this->transition;
    }

    public function setTransition(int $transition): void
    {
        $this->transition = $transition;
    }
}
