<?php

use CodeIgniter\Test\CIUnitTestCase;
use Tests\Support\Models\ExampleModel;

/**
 * @internal
 */
final class ExampleDatabaseTest extends CIUnitTestCase
{
    public function testExampleModelInstance(): void
    {
        $model = new ExampleModel();
        $this->assertInstanceOf(ExampleModel::class, $model);
    }
}

