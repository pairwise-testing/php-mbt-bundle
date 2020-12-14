<?php

namespace Tienvx\Bundle\MbtBundle\Model;

use DateTimeInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Tienvx\Bundle\MbtBundle\Model\Task\SeleniumConfigInterface;
use Tienvx\Bundle\MbtBundle\Model\Task\TaskConfigInterface;

class Task implements TaskInterface
{
    protected ?int $id;
    protected string $title;
    protected ModelInterface $model;
    protected UserInterface $user;
    protected bool $sendEmail;
    protected SeleniumConfigInterface $seleniumConfig;
    protected TaskConfigInterface $taskConfig;
    protected ProgressInterface $progress;
    protected DateTimeInterface $updatedAt;
    protected DateTimeInterface $createdAt;

    public function __construct()
    {
        $this->progress = new Progress();
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

    public function getModel(): ModelInterface
    {
        return $this->model;
    }

    public function setModel(ModelInterface $model): void
    {
        $this->model = $model;
    }

    public function getUser(): UserInterface
    {
        return $this->user;
    }

    public function setUser(UserInterface $user): void
    {
        $this->user = $user;
    }

    public function getSendEmail(): bool
    {
        return $this->sendEmail;
    }

    public function setSendEmail(bool $sendEmail): void
    {
        $this->sendEmail = $sendEmail;
    }

    public function getSeleniumConfig(): SeleniumConfigInterface
    {
        return $this->seleniumConfig;
    }

    public function setSeleniumConfig(SeleniumConfigInterface $seleniumConfig): void
    {
        $this->seleniumConfig = $seleniumConfig;
    }

    public function getTaskConfig(): TaskConfigInterface
    {
        return $this->taskConfig;
    }

    public function setTaskConfig(TaskConfigInterface $taskConfig): void
    {
        $this->taskConfig = $taskConfig;
    }

    public function getProgress(): ProgressInterface
    {
        return $this->progress;
    }

    public function setProgress(ProgressInterface $progress): void
    {
        $this->progress = $progress;
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
