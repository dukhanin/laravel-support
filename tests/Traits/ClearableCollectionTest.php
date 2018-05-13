<?php

namespace Dukhanin\Support\Tests;

use Dukhanin\Support\Tests\Stubs\ClearableCollectionStub;

class ClearableCollectionTest extends TestCase
{
    public function testClear()
    {
        $collection = new ClearableCollectionStub([
            'one' => 'One',
            'two' => 'Two',
            'four' => 'Four',
        ]);

        $this->assertCount(3, $collection);

        $collection->clear();

        $this->assertCount(0, $collection);
    }
}
