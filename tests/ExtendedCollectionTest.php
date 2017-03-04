<?php

namespace Tests\Unit;

use Dukhanin\Support\ExtendedCollection;
use PHPUnit\Framework\TestCase;

class ExtendedCollectionTest extends TestCase
{

    public function testUntouched()
    {
        $collection = new ExtendedCollection();

        $this->assertTrue($collection->touched() === false);
    }


    public function testTouch()
    {
        $collection = new ExtendedCollection();

        $collection->touch();

        $this->assertTrue($collection->touched() === true);
    }


    public function testTouchedWithConstructor()
    {
        $collection = new ExtendedCollection([
            'one' => 'one'
        ]);

        $this->assertTrue($collection->touched() === true);
    }


    public function testTouchedWithArrayAccess()
    {
        $collection = new ExtendedCollection();

        $collection['one'] = 'One';

        $this->assertTrue($collection->touched() === true);
    }


    public function testTouchedWithUnset()
    {
        $collection = new ExtendedCollection();

        unset( $collection['one'] );

        $this->assertTrue($collection->touched() === true);
    }


    public function testTouchedWithPut()
    {
        $collection = new ExtendedCollection();

        $collection->put('one', 'One');

        $this->assertTrue($collection->touched() === true);
    }


    public function testTouchedWithForget()
    {
        $collection = new ExtendedCollection();

        $collection->forget('one');

        $this->assertTrue($collection->touched() === true);
    }


    public function testConstructorInitialization()
    {
        $collection = new ExtendedCollection([
            // this items are not being resolved (no resolver yet)
            'one' => 'One',
            'two' => 'Two',
        ]);

        $collection->resolver(function (&$item, $key) {
            return false;
        });

        $this->assertTrue($collection->toArray() === [
                'one' => 'One',
                'two' => 'Two'
            ]);

        $this->assertTrue($collection->raw()->toArray() === [
                'one' => 'One',
                'two' => 'Two'
            ]);

    }


    public function testArrayAccess()
    {
        $collection = new ExtendedCollection([
            // this items are not being resolved (no resolver yet)
            'one' => 'One',
        ]);

        $collection->resolver(function (&$item, $key) {
            return $item . '-resolved';
        });

        $collection['two']   = 'Two';
        $collection['three'] = 'Three';

        $this->assertTrue($collection['one'] === 'One');
        $this->assertTrue($collection['two'] === 'Two-resolved');
        $this->assertTrue($collection['three'] === 'Three-resolved');

        $this->assertTrue($collection->raw()['one'] === 'One');
        $this->assertTrue($collection->raw()['two'] === 'Two');
        $this->assertTrue($collection->raw()['three'] === 'Three');

        unset( $collection['three'] );

        $this->assertTrue($collection->has('three') === false);
        $this->assertTrue($collection->raw()->has('three') === false);

    }


    public function testPop()
    {
        $collection = new ExtendedCollection();

        $collection->resolver(function (&$item, $key) {
            return $item . '-resolved';
        });

        $collection->put('one', 'One');
        $collection->put('two', 'Two');

        $this->assertTrue($collection->pop() === 'Two-resolved');

        $this->assertTrue($collection->get('two') === null);
        $this->assertTrue($collection->raw()->get('two') === null);
    }


    public function testPrepend()
    {
        $collection = new ExtendedCollection();

        $collection->resolver(function (&$item, $key) {
            return $item . '-resolved';
        });

        $collection->put('two', 'Two');
        $collection->prepend('One', 'one');

        $this->assertTrue($collection->first() === 'One-resolved');
        $this->assertTrue($collection->raw()->first() === 'One');
    }


    public function testPull()
    {
        $collection = new ExtendedCollection();

        $collection->resolver(function (&$item, $key) {
            return $item . '-resolved';
        });

        $collection->put('one', 'One');
        $collection->put('two', 'Two');

        $this->assertTrue($collection->pull('two') === 'Two-resolved');

        $this->assertTrue($collection->get('two') === null);
        $this->assertTrue($collection->raw()->get('two') === null);
    }


    public function testShift()
    {
        $collection = new ExtendedCollection();

        $collection->resolver(function (&$item, $key) {
            return $item . '-resolved';
        });

        $collection->put('one', 'One');
        $collection->put('two', 'Two');

        $this->assertTrue($collection->shift() === 'One-resolved');

        $this->assertTrue($collection->get('one') === null);
        $this->assertTrue($collection->raw()->get('one') === null);
    }


    public function testAfter()
    {
        $collection = new ExtendedCollection();

        $collection->resolver(function (&$item, $key) {
            return $item . '-resolved';
        });

        $collection->put('one', 'One');
        $collection->put('three', 'Three');
        $collection->after('two', 'Two', 'one');

        $this->assertTrue($collection->toArray() === [
                'one'   => 'One-resolved',
                'two'   => 'Two-resolved',
                'three' => 'Three-resolved'
            ]);

        $this->assertTrue($collection->raw()->toArray() === [
                'one'   => 'One',
                'two'   => 'Two',
                'three' => 'Three'
            ]);
    }


    public function testBefore()
    {
        $collection = new ExtendedCollection();

        $collection->resolver(function (&$item, $key) {
            return $item . '-resolved';
        });

        $collection->put('one', 'One');
        $collection->put('three', 'Three');
        $collection->before('two', 'Two', 'three');

        $this->assertTrue($collection->toArray() === [
                'one'   => 'One-resolved',
                'two'   => 'Two-resolved',
                'three' => 'Three-resolved'
            ]);

        $this->assertTrue($collection->raw()->toArray() === [
                'one'   => 'One',
                'two'   => 'Two',
                'three' => 'Three'
            ]);
    }
}