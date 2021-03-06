<?php

namespace Tienvx\Bundle\MbtBundle\Command;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use SingleColorPetrinet\Model\ColorInterface;
use Tienvx\Bundle\MbtBundle\Model\Model\Revision\CommandInterface;

interface CommandRunnerManagerInterface
{
    public function getAllCommands(): array;

    public function getCommandsRequireTarget(): array;

    public function getCommandsRequireValue(): array;

    public function validateTarget(CommandInterface $command): bool;

    public function run(CommandInterface $command, ColorInterface $color, RemoteWebDriver $driver): void;
}
