<?php

namespace Dukhanin\Support\Tests;

use Carbon\Carbon;
use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use Dukhanin\Support\Date;
use Dukhanin\Support\Tests\Stubs\CastsDatesStub;

class CastsDatesTest extends TestCase
{
    protected $config;

    public function setUp()
    {
        $this->config = Container::getInstance()->config = new Repository([
            'date' => require __DIR__.'/../../config/date.php',
        ]);
    }

    public function testEmptyAttributeIsNull()
    {
        $model = new CastsDatesStub();

        $this->assertNull($model->date);
        $this->assertNull($model->datetime);
    }

    public function testAttributeIsNullAfterReset()
    {
        $model = new CastsDatesStub();

        $model->date = '2017-10-27 12:20:00';
        $model->datetime = '2018-11-28 14:30:00';

        $model->date = null;
        $model->datetime = null;

        $this->assertNull($model->date);
        $this->assertNull($model->datetime);
    }

    public function testAttributeIsDateObject()
    {
        $model = new CastsDatesStub();

        $model->date = '2017-10-27 12:20:00';
        $model->datetime = '2018-11-28 14:30:00';

        $this->assertInstanceOf(Date::class, $model->date);
        $this->assertInstanceOf(Date::class, $model->datetime);
    }

    public function testAttributeUsesConfigFormatWhenRenders()
    {
        $this->config->set('date', [
            'to_string' => [
                'date' => 'Y j F',
                'datetime' => 'Y j F H:i',
            ],
        ]);

        $model = new CastsDatesStub();

        $model->date = '2017-10-27 12:20:00';
        $model->datetime = '2018-11-28 14:30:00';

        $this->assertEquals('2017 27 October', $model->date->__toString());
        $this->assertEquals('2018 28 November 14:30', $model->datetime->__toString());
    }

    public function testDateObjectIsSet()
    {
        $model = new CastsDatesStub();

        $model->date = new Date('2017-10-27 12:20:00');
        $model->datetime = new Date('2018-11-28 14:30:00');

        $this->assertInstanceOf(Date::class, $model->date);
        $this->assertInstanceOf(Date::class, $model->datetime);

        $this->assertTrue($model->date->equalTo(Carbon::parse('2017-10-27 00:00:00')));
        $this->assertTrue($model->datetime->equalTo(Carbon::parse('2018-11-28 14:30:00')));
    }

    public function testDateTimeInterfaceObjectIsSet()
    {
        $model = new CastsDatesStub();

        $model->date = Carbon::parse('2017-10-27 12:20:00');
        $model->datetime = Carbon::parse('2018-11-28 14:30:00');

        $this->assertInstanceOf(Date::class, $model->date);
        $this->assertInstanceOf(Date::class, $model->datetime);

        $this->assertTrue($model->date->equalTo(Carbon::parse('2017-10-27 00:00:00')));
        $this->assertTrue($model->datetime->equalTo(Carbon::parse('2018-11-28 14:30:00')));
    }

    public function testTimestampIsSet()
    {
        $model = new CastsDatesStub();

        $model->date = Carbon::parse('2017-10-27 12:20:00')->timestamp;
        $model->datetime = Carbon::parse('2018-11-28 14:30:00')->timestamp;

        $this->assertInstanceOf(Date::class, $model->date);
        $this->assertInstanceOf(Date::class, $model->datetime);

        $this->assertTrue($model->date->equalTo(Carbon::parse('2017-10-27 00:00:00')));
        $this->assertTrue($model->datetime->equalTo(Carbon::parse('2018-11-28 14:30:00')));
    }

    public function testStandardDateIsSet()
    {
        $model = new CastsDatesStub();

        $model->date = '2017-10-27';
        $model->datetime = '2018-11-28';

        $this->assertInstanceOf(Date::class, $model->date);
        $this->assertInstanceOf(Date::class, $model->datetime);

        $this->assertTrue($model->date->equalTo(Carbon::parse('2017-10-27 00:00:00')));
        $this->assertTrue($model->datetime->equalTo(Carbon::parse('2018-11-28 00:00:00')));
    }
}
