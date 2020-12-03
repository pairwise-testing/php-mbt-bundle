<?php

namespace Tienvx\Bundle\MbtBundle\Entity\Model;

use Symfony\Component\Validator\Constraints as Assert;
use Tienvx\Bundle\MbtBundle\Model\Model\Place as PlaceModel;

class Place extends PlaceModel
{
    /**
     * @Assert\NotBlank
     * @Assert\Type("string")
     */
    protected string $label;

    /**
     * @Assert\Type("bool")
     */
    protected bool $init = false;

    /**
     * @Assert\All({
     *     @Assert\Type("\Tienvx\Bundle\MbtBundle\Entity\Model\Command")
     *     @Assert\Valid
     * })
     */
    protected array $assertions = [];
}
