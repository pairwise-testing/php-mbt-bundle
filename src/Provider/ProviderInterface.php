<?php

namespace Tienvx\Bundle\MbtBundle\Provider;

use Facebook\WebDriver\Remote\DesiredCapabilities;
use Tienvx\Bundle\MbtBundle\Model\TaskInterface;
use Tienvx\Bundle\MbtBundle\Plugin\PluginInterface;

interface ProviderInterface extends PluginInterface
{
    public const RESOLUTIONS = 'resolutions';
    public const BROWSERS = 'browsers';

    public function getSeleniumServerUrl(string $seleniumServer): string;

    public function getCapabilities(TaskInterface $task, ?int $recordVideoBugId = null): DesiredCapabilities;

    public function getVideoUrl(string $seleniumServer, int $bugId): string;

    public static function getOperatingSystems(): array;

    public static function getBrowsers(string $platform): array;

    public static function getResolutions(string $platform): array;
}
