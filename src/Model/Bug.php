<?php

namespace Tienvx\Bundle\MbtBundle\Model;

use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Tienvx\Bundle\MbtBundle\Model\Bug\StepInterface;

class Bug implements BugInterface
{
    protected ?int $id;

    protected string $title;

    protected Collection $steps;

    protected ModelInterface $model;

    protected string $message;

    protected ProgressInterface $progress;

    protected bool $closed = false;

    protected int $petrinetVersion;

    protected DateTimeInterface $updatedAt;

    protected DateTimeInterface $createdAt;

    public function __construct()
    {
        $this->progress = new Progress();
        $this->steps = new ArrayCollection();
    }

    public function setId(int $id)
    {
        $this->id = $id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getSteps(): Collection
    {
        return $this->steps;
    }

    public function setSteps(iterable $steps): void
    {
        $this->steps = new ArrayCollection();
        foreach ($steps as $step) {
            $this->addStep($step);
        }
    }

    public function addStep(StepInterface $step): void
    {
        if (!$this->steps->contains($step)) {
            $this->steps->add($step);
        }
    }

    public function removeStep(StepInterface $step): void
    {
        if ($this->steps->contains($step)) {
            $this->steps->removeElement($step);
        }
    }

    public function getModel(): ModelInterface
    {
        return $this->model;
    }

    public function setModel(ModelInterface $model): void
    {
        $this->model = $model;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage(string $message): void
    {
        $this->message = $message;
    }

    public function getProgress(): ProgressInterface
    {
        return $this->progress;
    }

    public function setProgress(ProgressInterface $progress): void
    {
        $this->progress = $progress;
    }

    public function isClosed(): bool
    {
        return $this->closed;
    }

    public function setClosed(bool $closed): void
    {
        $this->closed = $closed;
    }

    public function getPetrinetVersion(): int
    {
        return $this->petrinetVersion;
    }

    public function setPetrinetVersion(int $petrinetVersion): void
    {
        $this->petrinetVersion = $petrinetVersion;
    }

    public function setCreatedAt(DateTimeInterface $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getCreatedAt(): DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setUpdatedAt(DateTimeInterface $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    public function getUpdatedAt(): DateTimeInterface
    {
        return $this->updatedAt;
    }
}
