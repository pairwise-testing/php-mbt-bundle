<?php

namespace Tienvx\Bundle\MbtBundle\Provider;

use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\WebDriverCapabilityType;
use Facebook\WebDriver\WebDriverPlatform;
use Tienvx\Bundle\MbtBundle\Exception\ExceptionInterface;
use Tienvx\Bundle\MbtBundle\Model\TaskInterface;

class Selenoid extends AbstractProvider
{
    public static function getName(): string
    {
        return 'selenoid';
    }

    /**
     * @throws ExceptionInterface
     */
    public function getVideoUrl(int $bugId): string
    {
        return sprintf('%s/video/bug-%s.mp4', $this->seleniumServer, $bugId);
    }

    public function getCapabilities(TaskInterface $task, ?int $recordVideoBugId = null): DesiredCapabilities
    {
        $caps = [];
        if ($recordVideoBugId) {
            $caps += [
                'enableVideo' => true,
                'videoName' => sprintf('bug-%d.mp4', $recordVideoBugId),
                'name' => sprintf("Recording video for bug %d", $recordVideoBugId),
            ];
        }
        if ($task->getPlatform() === WebDriverPlatform::ANDROID) {
            $caps += [
                'skin' => $task->getResolution(),
            ];
        } else {
            $caps += [
                'screenResolution' => "{$task->getResolution()}x24",
            ];
        }
        $caps += [
            WebDriverCapabilityType::BROWSER_NAME => $task->getBrowser(),
            WebDriverCapabilityType::VERSION => $task->getBrowserVersion(),
            WebDriverCapabilityType::PLATFORM => $task->getPlatform(),
            'enableVNC' => true,
            'enableLog' => true,
            'name' => sprintf("Executing task %d", $task->getId()),
        ];
        return new DesiredCapabilities($caps);
    }

    protected static function loadConfig(): array
    {
        return require __DIR__ . '/../Resources/providers/selenoid.php';
    }
}
