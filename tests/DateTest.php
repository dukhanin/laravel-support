<?php
namespace Dukhanin\Support\Tests;

use Dukhanin\Support\Date;

class DateTest extends TestCase
{
    public function testSetCustomToStringFormat()
    {
        $time = time();

        $date = Date::parse($time);
        $date->setCustomToStringFormat('Y.m.d.H.i.s');

        $this->assertEquals(date('Y.m.d.H.i.s', $time), $date->__toString());
    }

    public function testResetCustomToStringFormat()
    {
        $time = time();

        $date = Date::parse($time);
        $date->setCustomToStringFormat('Y.m.d.H.i.s');
        $date->resetCustomToStringFormat();

        $this->assertNotEquals(date('Y.m.d.H.i.s', $time), $date->__toString());
    }
}