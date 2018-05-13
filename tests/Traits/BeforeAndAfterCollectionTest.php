<?php

namespace Dukhanin\Support\Tests;

use Dukhanin\Support\Tests\Stubs\BeforeAndAfterCollectionStub;

class BeforeAndAfterCollectionTest extends TestCase
{
    public function testBeforeWithNewItem()
    {
        $collection = new BeforeAndAfterCollectionStub([
            'one' => 'One',
            'two' => 'Two',
            // inserting 'Three' here (before 'Four')
            'four' => 'Four',
        ]);

        $collection->before('three', 'Three', 'four');

        $this->assertEquals([
            'one' => 'One',
            'two' => 'Two',
            'three' => 'Three',
            'four' => 'Four',
        ], $collection->toArray());
    }

    public function testBeforeWithExistsItem()
    {
        $collection = new BeforeAndAfterCollectionStub([
            'three' => 'Not three',
            'one' => 'One',
            'two' => 'Two',
            // moving 'Not three' here (before four)
            // with replaced value to 'Three'
            'four' => 'Four',
        ]);

        $collection->before('three', 'Three', 'four');

        $this->assertEquals([
            'one' => 'One',
            'two' => 'Two',
            'three' => 'Three',
            'four' => 'Four',
        ], $collection->toArray());
    }

    public function testBeforeWithUnexistsNeighbor()
    {
        $collection = new BeforeAndAfterCollectionStub([ 
            // inserting 'One' here (at the begining of array)
            'two' => 'Two',
            'three' => 'Three',
        ]);

        $collection->before('one', 'One', 'dummy');

        $this->assertEquals([
            'one' => 'One',
            'two' => 'Two',
            'three' => 'Three',
        ], $collection->toArray());
    }

    public function testBeforeWithEmptyCollection()
    {
        $collection = new BeforeAndAfterCollectionStub();
        
        $collection->before('two', 'Two');
        $collection->before('one', 'One');

        $this->assertEquals([
            'one' => 'One',
            'two' => 'Two',
        ], $collection->toArray());
    }

    public function testBeforeWithNullKeys()
    {
        
        $collection = new BeforeAndAfterCollectionStub([
            // inserting 'One' here (at the begining of array)
            'two' => 'Two',
            'three' => 'Three',
        ]);

        $collection->before(null, 'One');

        $this->assertEquals([
            'One',
            'two' => 'Two',
            'three' => 'Three',
        ], $collection->toArray());
    }

    public function testAfterWithNewItem()
    {
        $collection = new BeforeAndAfterCollectionStub([
            'one' => 'One',
            'two' => 'Two',
            // inserting 'Three' here (after two)
            'four' => 'Four',
        ]);

        $collection->after('three', 'Three', 'two');

        $this->assertEquals([
            'one' => 'One',
            'two' => 'Two',
            'three' => 'Three',
            'four' => 'Four',
        ], $collection->toArray());
    }

    public function testAfterWithExistsItem()
    {
        $collection = new BeforeAndAfterCollectionStub([
            'three' => 'Not three',
            'one' => 'One',
            'two' => 'Two',
            // moving 'Not three' here (after two)
            // with replaced value to 'Three'
            'four' => 'Four',
        ]);

        $collection->after('three', 'Three', 'two');

        $this->assertEquals([
            'one' => 'One',
            'two' => 'Two',
            'three' => 'Three',
            'four' => 'Four',
        ], $collection->toArray());
    }

    public function testAfterWithUnexistsNeighbor()
    {
        $collection = new BeforeAndAfterCollectionStub([
            'one' => 'One',
            'two' => 'Two'
            // inserting 'Three' here (at the end of array)
        ]);

        $collection->after('three', 'Three', 'dummy');

        $this->assertEquals([
            'one' => 'One',
            'two' => 'Two',
            'three' => 'Three',
        ], $collection->toArray());
    }

    public function testAfterWithEmptyCollection()
    {
        $collection = new BeforeAndAfterCollectionStub();

        $collection->after('one', 'One');
        $collection->after('two', 'Two');

        $this->assertEquals([
            'one' => 'One',
            'two' => 'Two',
        ], $collection->toArray());
    }

    public function testAfterWithNullKeys()
    {
        $collection = new BeforeAndAfterCollectionStub([
            'one' => 'One',
            'two' => 'Two'
            // inserting 'Three' here  (at the end of array)
        ]);

        $collection->after(null, 'Three');

        $this->assertEquals([
            'one' => 'One',
            'two' => 'Two',
            'Three',
        ], $collection->toArray());
    }
}
