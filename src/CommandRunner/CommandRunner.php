<?php

namespace Tienvx\Bundle\MbtBundle\CommandRunner;

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverElement;
use Facebook\WebDriver\WebDriverSelect;
use Tienvx\Bundle\MbtBundle\Exception\UnexpectedValueException;
use Tienvx\Bundle\MbtBundle\Model\Model\CommandInterface;

abstract class CommandRunner implements CommandRunnerInterface
{
    public const ALL_COMMANDS = [];

    public function getAssertions(): array
    {
        return [];
    }

    public function supports(CommandInterface $command): bool
    {
        return in_array($command->getCommand(), static::ALL_COMMANDS);
    }

    protected function getSelector(string $target): WebDriverBy
    {
        $match = preg_match('/^(id|name|linkText|partialLinkText|css|xpath)=(.*)/i', $target, $matches);
        if (!$match) {
            throw new UnexpectedValueException('Invalid target');
        }

        list(, $mechanism, $value) = $matches;
        switch ($mechanism) {
            case 'id':
                return WebDriverBy::id($value);
            case 'name':
                return WebDriverBy::name($value);
            case 'linkText':
                return WebDriverBy::linkText($value);
            case 'partialLinkText':
                return WebDriverBy::partialLinkText($value);
            case 'css':
                return WebDriverBy::cssSelector($value);
            case 'xpath':
                return WebDriverBy::xpath($value);
        }

        throw new UnexpectedValueException('Invalid target mechanism');
    }

    protected function isElementEditable(RemoteWebDriver $driver, WebDriverElement $element): bool
    {
        $result = $driver->executeScript(
            'return { enabled: !arguments[0].disabled, readonly: arguments[0].readOnly };',
            [$element]
        );

        return $result->enabled && !$result->readonly;
    }

    protected function getSelect(WebDriverElement $element): WebDriverSelect
    {
        return new WebDriverSelect($element);
    }
}