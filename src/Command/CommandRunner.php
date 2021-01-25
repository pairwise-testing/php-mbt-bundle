<?php

namespace Tienvx\Bundle\MbtBundle\Command;

use Facebook\WebDriver\Exception\UnexpectedTagNameException;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverElement;
use Facebook\WebDriver\WebDriverSelect;
use Tienvx\Bundle\MbtBundle\Exception\UnexpectedValueException;
use Tienvx\Bundle\MbtBundle\Model\Model\Revision\CommandInterface;

abstract class CommandRunner implements CommandRunnerInterface
{
    public function supports(CommandInterface $command): bool
    {
        return in_array($command->getCommand(), $this->getAllCommands());
    }

    protected function isValidSelector(string $target): bool
    {
        list($mechanism) = explode('=', $target, 2);

        return in_array($mechanism, static::MECHANISMS);
    }

    protected function getSelector(string $target): WebDriverBy
    {
        list($mechanism, $value) = explode('=', $target, 2);
        switch ($mechanism) {
            case static::MECHANISM_ID:
            case static::MECHANISM_NAME:
            case static::MECHANISM_LINK_TEXT:
            case static::MECHANISM_PARTIAL_LINK_TEXT:
            case static::MECHANISM_XPATH:
                return WebDriverBy::{$mechanism}($value);
            case static::MECHANISM_CSS:
                return WebDriverBy::cssSelector($value);
            default:
                throw new UnexpectedValueException('Invalid target mechanism');
        }
    }

    protected function isElementEditable(RemoteWebDriver $driver, WebDriverElement $element): bool
    {
        $result = $driver->executeScript(
            'return { enabled: !arguments[0].disabled, readonly: arguments[0].readOnly };',
            [$element]
        );

        return $result->enabled && !$result->readonly;
    }

    /**
     * @throws UnexpectedTagNameException
     */
    protected function getSelect(WebDriverElement $element): WebDriverSelect
    {
        return new WebDriverSelect($element);
    }
}
