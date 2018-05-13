<?php

namespace Dukhanin\Support\Tests;

use Dukhanin\Support\Tests\Stubs\TouchableStub;

class TouchableTest extends TestCase
{
    public function testTouch()
    {
        $collection = new TouchableStub();

        $this->assertFalse($collection->touched());

        $collection->touch();

        $this->assertTrue($collection->touched());
    }
}
