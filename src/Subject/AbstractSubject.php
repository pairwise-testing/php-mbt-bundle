<?php

namespace Tienvx\Bundle\MbtBundle\Subject;

use League\Flysystem\FileNotFoundException;
use League\Flysystem\FilesystemInterface;
use Tienvx\Bundle\MbtBundle\Entity\StepData;

abstract class AbstractSubject implements SubjectInterface
{
    /**
     * @var mixed Required by workflow component
     */
    private $marking;

    /**
     * @var mixed Required by workflow component
     */
    private $context;

    /**
     * @var bool
     */
    protected $testingModel = false;

    /**
     * @var bool
     */
    protected $testingSubject = false;

    /**
     * @var StepData
     */
    protected $data;

    /**
     * @var StepData
     */
    protected $storedData;

    /**
     * @var bool
     */
    protected $needData = true;

    /**
     * @var FilesystemInterface
     */
    protected $filesystem;

    public function __construct($marking = null)
    {
        $this->marking = $marking;
        $this->context = [];
        $this->data = new StepData();
        $this->storedData = new StepData();
    }

    public function getMarking()
    {
        return $this->marking;
    }

    public function setMarking($marking, array $context = [])
    {
        $this->marking = $marking;
        $this->context = $context;
    }

    public function getContext(): array
    {
        return $this->context;
    }

    public static function support(): bool
    {
        return true;
    }

    /**
     * @param bool $testingModel
     */
    public function setTestingModel(bool $testingModel = false)
    {
        $this->testingModel = $testingModel;
    }

    /**
     * @return bool
     */
    public function isTestingModel()
    {
        return $this->testingModel;
    }

    /**
     * @param bool $testingSubject
     */
    public function setTestingSubject(bool $testingSubject = false)
    {
        $this->testingSubject = $testingSubject;
    }

    /**
     * @return bool
     */
    public function isTestingSubject()
    {
        return $this->testingSubject;
    }

    /**
     * @param bool $needData
     */
    public function setNeedData(bool $needData = true)
    {
        $this->needData = $needData;
    }

    /**
     * @return bool
     */
    public function needData()
    {
        return $this->needData;
    }

    /**
     * @param StepData $data
     */
    public function setData(StepData $data)
    {
        $this->data = $data;
    }

    /**
     * @return StepData
     */
    public function getData(): StepData
    {
        return $this->data;
    }

    public function storeData()
    {
        $this->storedData = $this->data;
    }

    /**
     * @return StepData
     */
    public function getStoredData(): StepData
    {
        return $this->storedData;
    }

    public function applyTransition(string $transitionName)
    {
        if (method_exists($this, $transitionName)) {
            call_user_func([$this, $transitionName]);
        }
    }

    public function enterPlace(array $places)
    {
        foreach ($places as $place) {
            if (method_exists($this, $place)) {
                call_user_func([$this, $place]);
            }
        }
    }

    /**
     * @param FilesystemInterface $filesystem
     */
    public function setFilesystem(FilesystemInterface $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    public function captureScreenshot($bugId, $index)
    {
        $this->filesystem->put("{$bugId}/{$index}.png", '');
    }

    public function getScreenshot($bugId, $index)
    {
        try {
            return $this->filesystem->read("{$bugId}/{$index}.png");
        } catch (FileNotFoundException $e) {
            return '';
        }
    }

    public function isImageScreenshot()
    {
        return true;
    }

    public function hasScreenshot($bugId, $index)
    {
        return $this->filesystem->has("{$bugId}/{$index}.png");
    }

    /**
     * @param $bugId
     *
     * @see https://stackoverflow.com/a/13468943
     */
    public function removeScreenshots($bugId)
    {
        $this->filesystem->deleteDir("$bugId/");
    }

    public function getScreenshotUrl($bugId, $index)
    {
        return '';
    }

    public function setUp()
    {
        // Init system-under-test connection e.g.
        // $this->client = Client::createChromeClient();
    }

    public function tearDown()
    {
        // Destroy system-under-test connection e.g.
        // $this->client->quit();
    }
}
