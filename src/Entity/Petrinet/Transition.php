<?php

namespace Tienvx\Bundle\MbtBundle\Entity\Petrinet;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tienvx\Bundle\MbtBundle\Model\Petrinet\PetrinetInterface;
use Tienvx\Bundle\MbtBundle\Model\Petrinet\Transition as BaseTransition;

/**
 * @ORM\Entity
 * @ORM\Table(name="transition")
 */
class Transition extends BaseTransition
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\OneToMany(
     *   targetEntity="Tienvx\Bundle\MbtBundle\Entity\Petrinet\InputArc",
     *   mappedBy="transition",
     *   orphanRemoval=true,
     *   cascade={"persist", "remove"}
     * )
     */
    protected $inputArcs;

    /**
     * @ORM\OneToMany(
     *   targetEntity="Tienvx\Bundle\MbtBundle\Entity\Petrinet\OutputArc",
     *   mappedBy="transition",
     *   orphanRemoval=true,
     *   cascade={"persist", "remove"}
     * )
     */
    protected $outputArcs;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @Assert\Type("string")
     */
    protected $guard = null;

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank
     * @Assert\Type("string")
     */
    protected string $label;

    /**
     * @ORM\OneToMany(
     *   targetEntity="Tienvx\Bundle\MbtBundle\Entity\Selenium\Command",
     *   mappedBy="transition",
     *   orphanRemoval=true,
     *   cascade={"persist", "remove"}
     * )
     */
    protected Collection $actions;

    /**
     * @ORM\ManyToOne(targetEntity="Tienvx\Bundle\MbtBundle\Entity\Petrinet\Petrinet", inversedBy="transitions")
     */
    protected PetrinetInterface $petrinet;
}
