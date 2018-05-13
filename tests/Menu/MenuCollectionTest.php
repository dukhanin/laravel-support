<?php
namespace Dukhanin\Support\Tests\Menu;

use Dukhanin\Support\Menu\MenuCollection;
use Dukhanin\Support\Menu\MenuItem;
use Dukhanin\Support\Tests\TestCase;

class MenuCollectionTest extends TestCase
{
    public function testConstructor()
    {
        $collection = new MenuCollection([
            'one' => 'One',
            'two.three' => 'Three',
        ]);

        $this->assertEquals('One', $collection->offsetGet('one')->label);
        $this->assertEquals('Three', $collection->offsetGet('two')->items()->offsetGet('three')->label);
    }

    public function testOffsetGet()
    {
        $collection = new MenuCollection([
            'one' => 'One',
            'two.three' => 'Three',
        ]);

        $this->assertEquals('One', $collection->offsetGet('one')->label);
        $this->assertEquals('Three', $collection->offsetGet('two')->items()->offsetGet('three')->label);
        $this->assertEquals('Three', $collection->offsetGet('two.three')->label);
    }

    public function testOffsetSet()
    {
        $collection = new MenuCollection;

        $collection->offsetSet('one', 'One');
        $collection->offsetSet('two.three', 'Three');

        $this->assertEquals('One', $collection->offsetGet('one')->label);
        $this->assertEquals('Three', $collection->offsetGet('two')->items()->offsetGet('three')->label);
    }

    public function testOffsetExists()
    {
        $collection = new MenuCollection([
            'one' => 'One',
            'two.three' => 'Three',
        ]);

        $this->assertTrue($collection->offsetExists('one'));
        $this->assertTrue($collection->offsetExists('two'));
        $this->assertTrue($collection->offsetExists('two.three'));

        $this->assertFalse($collection->offsetExists('One'));
        $this->assertFalse($collection->offsetExists('Two'));
        $this->assertFalse($collection->offsetExists('two.Three'));
    }

    public function testOffsetUnset()
    {
        $collection = new MenuCollection([
            'one' => 'One',
            'two.three' => 'Three',
        ]);

        $this->assertTrue($collection->offsetExists('one'));
        $this->assertTrue($collection->offsetExists('two'));
        $this->assertTrue($collection->offsetExists('two.three'));

        $collection->offsetUnset('one');
        $collection->offsetUnset('two.three');

        $this->assertFalse($collection->offsetExists('one'));
        $this->assertTrue($collection->offsetExists('two'));
        $this->assertFalse($collection->offsetExists('two.three'));

        $collection->offsetUnset('two');
        $this->assertFalse($collection->offsetExists('two'));
    }

    public function testSetItemClass()
    {
        $collection = new MenuCollection;
        $collection->setItemClass(MenuItemMock::class);

        $collection->offsetSet('one', 'One');
        $collection->offsetSet('two.three', 'Three');

        $this->assertInstanceOf(MenuItemMock::class, $collection->offsetGet('one'));
        $this->assertInstanceOf(MenuItemMock::class, $collection->offsetGet('two'));
        $this->assertInstanceOf(MenuItemMock::class, $collection->offsetGet('two.three'));
    }

    public function testResolve()
    {
        $one = new MenuItem(['label' => 'One']);

        $collection = new MenuCollection;

        $collection->offsetSet('one', $one);
        $collection->offsetSet('two.three', 'Three');

        $this->assertEquals($one, $collection->offsetGet('one'));
    }

    public function testEnabled()
    {
        $one = new MenuItem(['label' => 'One', 'enabled' => false]);
        $two = new MenuItem(['label' => 'Two', 'enabled' => false]);

        $collection = new MenuCollection;
        $collection->push($one);
        $collection->push($two);

        $this->assertCount(0, $collection->enabled());

        $one->enabled = true;
        $this->assertCount(1, $collection->enabled());
        $this->assertEquals($one, $collection->enabled()->first());
    }

    public function testHasEnabled()
    {
        $one = new MenuItem(['label' => 'One', 'enabled' => false]);
        $two = new MenuItem(['label' => 'Two', 'enabled' => false]);

        $collection = new MenuCollection;
        $collection->push($one);
        $collection->push($two);

        $this->assertFalse($collection->hasEnabled());

        $one->enabled = true;
        $this->assertTrue($collection->hasEnabled());
    }

    public function testHasActive()
    {
        $one = new MenuItem(['label' => 'One', 'active' => false]);
        $two = new MenuItem(['label' => 'Two', 'active' => false]);

        $collection = new MenuCollection;
        $collection->push($one);
        $collection->push($two);

        $this->assertFalse($collection->hasActive());

        $one->active = true;
        $this->assertTrue($collection->hasActive());
    }

    public function testPrepend()
    {
        $collection = new MenuCollection;

        $collection->push($one = new MenuItem(['label' => 'One']));
        $this->assertEquals($one, $collection->first());

        $collection->prepend($two = new MenuItem(['label' => 'Two']));
        $this->assertCount(2, $collection);
        $this->assertEquals($two, $collection->first());
    }

    public function testBeforeWithoutKey()
    {
        $collection = new MenuCollection();

        $collection->put('two', $two = new MenuItem(['label' => 'Two']));
        $collection->put('three', $three = new MenuItem(['label' => 'Three']));
        $collection->before('one', $one = new MenuItem(['label' => 'One']));

        $this->assertCount(3, $collection);
        $this->assertEquals($one, $collection->first());
        $this->assertEquals($two, $collection->slice(1,1)->first());
        $this->assertEquals($three, $collection->slice(2,1)->first());
    }

    public function testBeforeWithKey()
    {
        $collection = new MenuCollection();

        $collection->put('one', $one = new MenuItem(['label' => 'One']));
        $collection->put('three', $three = new MenuItem(['label' => 'Three']));
        $collection->before('two', $two = new MenuItem(['label' => 'Two']), 'three');

        $this->assertCount(3, $collection);
        $this->assertEquals($one, $collection->first());
        $this->assertEquals($two, $collection->slice(1,1)->first());
        $this->assertEquals($three, $collection->slice(2,1)->first());
    }

    public function testBeforeWithExists()
    {
        $collection = new MenuCollection();

        $collection->put('one', $one = new MenuItem(['label' => 'One']));
        $collection->put('three', $three = new MenuItem(['label' => 'Three']));
        $collection->put('two', $two = new MenuItem(['label' => 'Two']));

        $collection->before('two', $two, 'three');

        $this->assertCount(3, $collection);
        $this->assertEquals($one, $collection->first());
        $this->assertEquals($two, $collection->slice(1,1)->first());
        $this->assertEquals($three, $collection->slice(2,1)->first());
    }

    public function testBeforeWithNested()
    {
        $collection = new MenuCollection();

        $collection->put('one', $one = new MenuItem(['label' => 'One']));
        $collection->put('two', $two = new MenuItem(['label' => 'Two']));
        $collection->put('two.a', $twoA = new MenuItem(['label' => 'Two A']));
        $collection->put('two.c', $twoC = new MenuItem(['label' => 'Two C']));
        $collection->put('three', $three = new MenuItem(['label' => 'Three']));

        $collection->before('b', $twoB = new MenuItem(['label' => 'Two B']), 'two.c');

        $this->assertCount(3, $collection);
        $this->assertEquals($one, $collection->first());
        $this->assertEquals($two, $collection->slice(1,1)->first());
        $this->assertEquals($three, $collection->slice(2,1)->first());

        $this->assertEquals($twoA, $two->items()->first());
        $this->assertEquals($twoB, $two->items()->slice(1,1)->first());
        $this->assertEquals($twoC, $two->items()->slice(2,1)->first());
    }
    
    /**/
    public function testAfterWithoutKey()
    {
        $collection = new MenuCollection();

        $collection->put('one', $one = new MenuItem(['label' => 'One']));
        $collection->put('two', $two = new MenuItem(['label' => 'Two']));
        $collection->after('three', $three = new MenuItem(['label' => 'Three']));

        $this->assertCount(3, $collection);
        $this->assertEquals($one, $collection->first());
        $this->assertEquals($two, $collection->slice(1,1)->first());
        $this->assertEquals($three, $collection->slice(2,1)->first());
    }

    public function testAfterWithKey()
    {
        $collection = new MenuCollection();

        $collection->put('one', $one = new MenuItem(['label' => 'One']));
        $collection->put('three', $three = new MenuItem(['label' => 'Three']));
        $collection->after('two', $two = new MenuItem(['label' => 'Two']), 'one');

        $this->assertCount(3, $collection);
        $this->assertEquals($one, $collection->first());
        $this->assertEquals($two, $collection->slice(1,1)->first());
        $this->assertEquals($three, $collection->slice(2,1)->first());
    }

    public function testAfterWithExists()
    {
        $collection = new MenuCollection();

        $collection->put('one', $one = new MenuItem(['label' => 'One']));
        $collection->put('three', $three = new MenuItem(['label' => 'Three']));
        $collection->put('two', $two = new MenuItem(['label' => 'Two']));

        $collection->after('two', $two, 'one');

        $this->assertCount(3, $collection);
        $this->assertEquals($one, $collection->first());
        $this->assertEquals($two, $collection->slice(1,1)->first());
        $this->assertEquals($three, $collection->slice(2,1)->first());
    }

    public function testAfterWithNested()
    {
        $collection = new MenuCollection();

        $collection->put('one', $one = new MenuItem(['label' => 'One']));
        $collection->put('two', $two = new MenuItem(['label' => 'Two']));
        $collection->put('two.a', $twoA = new MenuItem(['label' => 'Two A']));
        $collection->put('two.c', $twoC = new MenuItem(['label' => 'Two C']));
        $collection->put('three', $three = new MenuItem(['label' => 'Three']));

        $collection->after('b', $twoB = new MenuItem(['label' => 'Two B']), 'two.a');

        $this->assertCount(3, $collection);
        $this->assertEquals($one, $collection->first());
        $this->assertEquals($two, $collection->slice(1,1)->first());
        $this->assertEquals($three, $collection->slice(2,1)->first());

        $this->assertEquals($twoA, $two->items()->first());
        $this->assertEquals($twoB, $two->items()->slice(1,1)->first());
        $this->assertEquals($twoC, $two->items()->slice(2,1)->first());
    }

    public function testChunk()
    {
        $collection = new MenuCollection();

        $collection->put('one', $one = new MenuItem(['label' => 'One']));
        $collection->put('two', $two = new MenuItem(['label' => 'Two']));
        $collection->put('three', $three = new MenuItem(['label' => 'Three']));
        $collection->put('four', $four = new MenuItem(['label' => 'Four']));
        $collection->put('five', $five = new MenuItem(['label' => 'Five']));

        $chunks = $collection->chunk(2);

        $this->assertCount(3, $chunks);

        $this->assertCount(2, $firstChunk = $chunks->first());
        $this->assertTrue($firstChunk->offsetExists('one'));
        $this->assertTrue($firstChunk->offsetExists('two'));

        $this->assertCount(2, $secondChunk = $chunks->slice(1,1)->first());
        $this->assertTrue($secondChunk->offsetExists('three'));
        $this->assertTrue($secondChunk->offsetExists('four'));

        $this->assertCount(1, $thirdChunk = $chunks->slice(2,1)->first());
        $this->assertTrue($thirdChunk->offsetExists('five'));

    }
}
