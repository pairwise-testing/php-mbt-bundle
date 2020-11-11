<?php

namespace Tienvx\Bundle\MbtBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Tienvx\Bundle\MbtBundle\Entity\Model;

/**
 * @covers \Tienvx\Bundle\MbtBundle\Entity\Model
 * @covers \Tienvx\Bundle\MbtBundle\Model\Model
 */
class ModelTest extends TestCase
{
    public function testPrePersist(): void
    {
        $model = new Model();
        $this->assertNull($model->getCreatedAt());
        $this->assertNull($model->getUpdatedAt());
        $model->prePersist();
        $this->assertInstanceOf(\DateTime::class, $model->getCreatedAt());
        $this->assertInstanceOf(\DateTime::class, $model->getUpdatedAt());
    }

    public function testPreUpdate(): void
    {
        $model = new Model();
        $this->assertNull($model->getUpdatedAt());
        $model->preUpdate();
        $this->assertInstanceOf(\DateTime::class, $model->getUpdatedAt());
    }
}
