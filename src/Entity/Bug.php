<?php

namespace Tienvx\Bundle\MbtBundle\Entity;

use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tienvx\Bundle\MbtBundle\Model\Bug as BugModel;
use Tienvx\Bundle\MbtBundle\Model\Bug\StepsInterface;
use Tienvx\Bundle\MbtBundle\Model\ModelInterface;
use Tienvx\Bundle\MbtBundle\Model\ProgressInterface;

/**
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class Bug extends BugModel
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected ?int $id;

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank
     */
    protected string $title;

    /**
     * @ORM\OneToOne(targetEntity="Tienvx\Bundle\MbtBundle\Entity\Bug\Steps", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="steps_id", referencedColumnName="id", nullable=false)
     */
    protected StepsInterface $steps;

    /**
     * @ORM\OneToOne(targetEntity="Model")
     */
    protected ModelInterface $model;

    /**
     * @ORM\Column(type="text")
     */
    protected string $message;

    /**
     * @ORM\Embedded(class="Progress")
     */
    protected ProgressInterface $progress;

    /**
     * @ORM\Column(type="boolean")
     */
    protected bool $closed = false;

    /**
     * @ORM\Column(name="created_at", type="datetime", nullable=true)
     */
    protected ?DateTimeInterface $createdAt = null;

    /**
     * @ORM\Column(name="updated_at", type="datetime", nullable=true)
     */
    protected ?DateTimeInterface $updatedAt = null;

    public function __construct()
    {
        parent::__construct();
        $this->progress = new Progress();
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersist(): void
    {
        $this->createdAt = new DateTime();
        $this->updatedAt = new DateTime();
    }

    /**
     * @ORM\PreUpdate
     */
    public function preUpdate(): void
    {
        $this->updatedAt = new DateTime();
    }
}
