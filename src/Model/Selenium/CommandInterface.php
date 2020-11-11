<?php

namespace Tienvx\Bundle\MbtBundle\Model\Selenium;

use Tienvx\Bundle\MbtBundle\Model\Petrinet\PlaceInterface;
use Tienvx\Bundle\MbtBundle\Model\Petrinet\TransitionInterface;

interface CommandInterface
{
    public function getCommand(): string;

    public function setCommand(string $command): void;

    public function getTarget(): string;

    public function setTarget(string $target): void;

    public function getValue(): string;

    public function setValue(string $value): void;

    public function setPlace(PlaceInterface $place);

    public function getPlace(): PlaceInterface;

    public function setTransition(TransitionInterface $transition);

    public function getTransition(): TransitionInterface;
}
