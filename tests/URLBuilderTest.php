<?php

namespace Dukhanin\Support\Tests;

use Dukhanin\Support\URLBuilder;

class URLBuilderTest extends TestCase
{
    public function testCompile()
    {
        $url = new URLBuilder('http://dukhanin:testPass@antondukhanin.ru/One/Two?query1=value1&query2=value2#fragment');
        $this->assertEquals('http://dukhanin:testPass@antondukhanin.ru/One/Two?query1=value1&query2=value2#fragment',
            $url->compile());

        $url = new URLBuilder();
        $url->scheme('http')->user('dukhanin')->pass('testPass')->host('antondukhanin.ru')->path('One/Two')->query([
            'query1' => 'value1',
            'query2' => 'value2',
        ])->fragment('fragment');

        $this->assertEquals('http://dukhanin:testPass@antondukhanin.ru/One/Two?query1=value1&query2=value2#fragment',
            $url->compile());
    }

    public function testCaseSensitive()
    {
        $url = new URLBuilder;

        $url->caseSensitive(true);
        $this->assertTrue($url->caseSensitive());

        $url->caseSensitive(false);
        $this->assertFalse($url->caseSensitive());
    }

    public function testEncoded()
    {
        $url1 = new URLBuilder('http://духанин.рф/Один/Два?query=value#fragment');
        $url2 = new URLBuilder('http://xn--80ahntb6ao.xn--p1ai/%D0%9E%D0%B4%D0%B8%D0%BD/%D0%94%D0%B2%D0%B0?query=value#fragment');

        $url1->encoded(false);
        $url2->encoded(false);

        $this->assertFalse($url1->encoded());
        $this->assertFalse($url2->encoded());

        $this->assertEquals('духанин.рф', $url1->host());
        $this->assertEquals('духанин.рф', $url2->host());

        $this->assertEquals('Один/Два', $url1->path());
        $this->assertEquals('Один/Два', $url2->path());

        $this->assertEquals('http://духанин.рф/Один/Два?query=value#fragment', $url1->compile());
        $this->assertEquals('http://духанин.рф/Один/Два?query=value#fragment', $url2->compile());

        $url1->encoded(true);
        $url2->encoded(true);

        $this->assertTrue($url1->encoded());
        $this->assertTrue($url2->encoded());

        $this->assertEquals('xn--80ahntb6ao.xn--p1ai', $url1->host());
        $this->assertEquals('xn--80ahntb6ao.xn--p1ai', $url2->host());

        $this->assertEquals('%D0%9E%D0%B4%D0%B8%D0%BD/%D0%94%D0%B2%D0%B0', $url1->path());
        $this->assertEquals('%D0%9E%D0%B4%D0%B8%D0%BD/%D0%94%D0%B2%D0%B0', $url2->path());

        $this->assertEquals($url1->compile(),
            'http://xn--80ahntb6ao.xn--p1ai/%D0%9E%D0%B4%D0%B8%D0%BD/%D0%94%D0%B2%D0%B0?query=value#fragment');
        $this->assertEquals($url2->compile(),
            'http://xn--80ahntb6ao.xn--p1ai/%D0%9E%D0%B4%D0%B8%D0%BD/%D0%94%D0%B2%D0%B0?query=value#fragment');
    }

    public function testScheme()
    {
        $url = new URLBuilder('HTTP://antondukhanin.ru/One/Two?query=value#fragment');

        $this->assertEquals('http', $url->scheme());

        $url->scheme('HTTPS://');

        $this->assertEquals('https', $url->scheme());
    }

    public function testFragment()
    {
        $url = new URLBuilder('http://antondukhanin.ru/One/Two?query=value');

        $this->assertNull($url->fragment());

        $url->fragment('#fragment');

        $this->assertEquals('fragment', $url->fragment());
        $this->assertEquals('http://antondukhanin.ru/One/Two?query=value#fragment', $url->compile());
    }

    public function testSecure()
    {
        $url = new URLBuilder('http://antondukhanin.ru/One/Two?query=value#fragment');

        $this->assertFalse($url->secure());
        $this->assertEquals('http', $url->scheme());

        $url->secure(true);
        $this->assertTrue($url->secure());
        $this->assertEquals('https', $url->scheme());
    }

    public function testUser()
    {
        $url = new URLBuilder('http://antondukhanin.ru/One/Two?query=value#fragment');

        $url->user('dukhanin');
        $this->assertEquals('dukhanin', $url->user());
        $this->assertEquals('http://dukhanin@antondukhanin.ru/One/Two?query=value#fragment', $url->compile());
    }

    public function testPass()
    {
        $url = new URLBuilder('http://antondukhanin.ru/One/Two?query=value#fragment');

        $url->user('dukhanin');
        $url->pass('testPass');
        $this->assertEquals('testPass', $url->pass());
        $this->assertEquals('http://dukhanin:testPass@antondukhanin.ru/One/Two?query=value#fragment', $url->compile());
    }

    public function testPath()
    {
        $url = new URLBuilder('http://antondukhanin.ru/One/Two?query=value#fragment');

        $this->assertEquals('One/Two', $url->path());

        $url->path(false);
        $this->assertEquals('', $url->path());
        $this->assertEquals('http://antondukhanin.ru?query=value#fragment', $url->compile());

        $url->path('/');
        $this->assertEquals('', $url->path());
        $this->assertEquals('http://antondukhanin.ru?query=value#fragment', $url->compile());

        $url->path('/One/Two/');
        $this->assertEquals('One/Two', $url->path());
        $this->assertEquals('http://antondukhanin.ru/One/Two?query=value#fragment', $url->compile());

        $url->path(['One', 'Two', 'three']);
        $this->assertEquals('One/Two/three', $url->path());
        $this->assertEquals('http://antondukhanin.ru/One/Two/three?query=value#fragment', $url->compile());

        return $this;
    }

    public function testSegments()
    {
        $url = new URLBuilder('http://antondukhanin.ru/One/Two?query=value#fragment');

        $this->assertEquals($url->segments(), ['One', 'Two']);
    }

    public function testSegment()
    {
        $url = new URLBuilder('http://antondukhanin.ru/One/Two?query=value#fragment');

        $this->assertEquals('One', $url->segment(0));
        $this->assertEquals('Two', $url->segment(1));
        $this->assertNull($url->segment(2));
    }

    public function testAppend()
    {
        $url = new URLBuilder('http://antondukhanin.ru?query=value#fragment');

        $url->append('/One//Two/');
        $this->assertEquals('http://antondukhanin.ru/One/Two?query=value#fragment', $url->compile());

        $url->append('/Three/');
        $this->assertEquals('http://antondukhanin.ru/One/Two/Three?query=value#fragment', $url->compile());

        $url->append(['/Four', 'Five']);
        $this->assertEquals('http://antondukhanin.ru/One/Two/Three/Four/Five?query=value#fragment', $url->compile());
    }

    public function testPrepend()
    {
        $url = new URLBuilder('http://antondukhanin.ru?query=value#fragment');

        $url->prepend('/Three//Four/');
        $this->assertEquals('http://antondukhanin.ru/Three/Four?query=value#fragment', $url->compile());

        $url->prepend(['One/', '/Two/']);
        $this->assertEquals('http://antondukhanin.ru/One/Two/Three/Four?query=value#fragment', $url->compile());
    }

    public function testShift()
    {
        // checking common cases, other specific cases
        // are represented in testShiftPath() and testShiftSegment() methods

        $url = new URLBuilder('http://antondukhanin.ru/One/Two/Three/Four/Five?query=value#fragment');

        // fetching incorrect path begining
        $this->assertNull($url->shift('Ten/Eleven'));

        // fetching first two path segments
        $this->assertEquals('One/Two', $url->shift('One/Two'));
        $this->assertEquals('http://antondukhanin.ru/Three/Four/Five?query=value#fragment', $url->compile());

        // fetching the first segment (index=0 by default)
        $this->assertEquals('Three', $url->shift());
        $this->assertEquals('http://antondukhanin.ru/Four/Five?query=value#fragment', $url->compile());
    }

    public function testShiftPath()
    {
        $url = new URLBuilder('http://antondukhanin.ru/One/Two/Three/Four/Five?query=value#fragment');

        // fetching incorrect path begining
        $this->assertNull($url->shiftPath('Ten/Eleven'));

        // fetching first two path segments
        $this->assertEquals('One/Two', $url->shiftPath('/One//Two/'));
        $this->assertEquals('http://antondukhanin.ru/Three/Four/Five?query=value#fragment', $url->compile());

        // fetching first two path segments using an array as argument
        $this->assertEquals('Three/Four', $url->shiftPath(['Three', 'Four']));
        $this->assertEquals('http://antondukhanin.ru/Five?query=value#fragment', $url->compile());

        // testing caseSensitive mode
        $url = new URLBuilder('http://antondukhanin.ru/One/Two/Three/Four/Five?query=value#fragment');

        $url->caseSensitive(true);

        $this->assertNull($url->shiftPath('/one//two/'));
        $this->assertEquals('One/Two', $url->shiftPath('/One//Two/'));
        $this->assertNull($url->shiftPath(['three', 'four']));
        $this->assertEquals('Three/Four', $url->shiftPath(['Three', 'Four']));

        // testing not caseSensitive mode
        $url = new URLBuilder('http://antondukhanin.ru/One/Two/Three/Four/Five?query=value#fragment');

        $url->caseSensitive(false);

        $this->assertEquals('One/Two', $url->shiftPath('/one//two/'));
        $this->assertEquals('Three/Four', $url->shiftPath(['three', 'four']));
    }

    public function testShiftSegment()
    {
        $url = new URLBuilder('http://antondukhanin.ru/One/Two?query=value#fragment');

        $this->assertEquals('One', $url->shiftSegment());
        $this->assertEquals('http://antondukhanin.ru/Two?query=value#fragment', $url->compile());
    }

    public function testPop()
    {
        // checking common cases, other specific cases
        // are represented in testShiftPath() and testShiftSegment() methods

        $url = new URLBuilder('http://antondukhanin.ru/One/Two/Three/Four/Five?query=value#fragment');

        // fetching incorrect path ending
        $this->assertNull($url->pop('Ten/Eleven'));

        // fetching first two path segments
        $this->assertEquals('Four/Five', $url->pop('Four/Five'));
        $this->assertEquals('http://antondukhanin.ru/One/Two/Three?query=value#fragment', $url->compile());

        // fetching the last segment
        $this->assertEquals('Three', $url->pop());
        $this->assertEquals('http://antondukhanin.ru/One/Two?query=value#fragment', $url->compile());
    }

    public function testPopPath()
    {
        $url = new URLBuilder('http://antondukhanin.ru/One/Two/Three/Four/Five?query=value#fragment');

        // fetching incorrect path begining
        $this->assertNull($url->popPath('Ten/Eleven'));

        // fetching first two path segments
        $this->assertEquals('Four/Five', $url->popPath('/Four//Five/'));
        $this->assertEquals('http://antondukhanin.ru/One/Two/Three?query=value#fragment', $url->compile());

        // fetching first two path segments using an array as argument
        $this->assertEquals('Two/Three', $url->popPath(['Two', 'Three']));
        $this->assertEquals('http://antondukhanin.ru/One?query=value#fragment', $url->compile());

        // testing caseSensitive mode
        $url = new URLBuilder('http://antondukhanin.ru/One/Two/Three/Four/Five?query=value#fragment');

        $url->caseSensitive(true);

        $this->assertNull($url->popPath('/four//five/'));
        $this->assertEquals('Four/Five', $url->popPath('/Four//Five/'));
        $this->assertNull($url->popPath(['two', 'three']));
        $this->assertEquals('Two/Three', $url->popPath(['Two', 'Three']));

        // testing not caseSensitive mode
        $url = new URLBuilder('http://antondukhanin.ru/One/Two/Three/Four/Five?query=value#fragment');

        $url->caseSensitive(false);

        $this->assertEquals('Four/Five', $url->popPath('/four//five/'));
        $this->assertEquals('Two/Three', $url->popPath(['two', 'three']));
    }

    public function testPopSegment()
    {
        $url = new URLBuilder('http://antondukhanin.ru/One/Two?query=value#fragment');

        $this->assertEquals('Two', $url->popSegment());
        $this->assertEquals('http://antondukhanin.ru/One?query=value#fragment', $url->compile());
    }

    public function testQueryString()
    {
        $url = new URLBuilder('http://antondukhanin.ru/One/Two?query1=value1&query2=value2#fragment');

        $this->assertEquals('query1=value1&query2=value2', $url->queryString());

        $url = new URLBuilder('http://antondukhanin.ru/One/Two#fragment');

        $this->assertEquals('', $url->queryString());
    }

    public function testClearQuery()
    {
        $url = new URLBuilder('http://antondukhanin.ru/One/Two?query1=value1&query2=value2#fragment');

        $this->assertEquals('query1=value1&query2=value2', $url->queryString());

        $url->clearQuery();

        $this->assertEquals('', $url->queryString());
        $this->assertEquals('http://antondukhanin.ru/One/Two#fragment', $url->compile());
    }

    public function testQuery()
    {
        $url = new URLBuilder('http://antondukhanin.ru/One/Two?query1=value1');

        $url->query(['query2' => 'value2']);

        $this->assertEquals('value1', $url->query('query1'));
        $this->assertEquals('value2', $url->query('query2'));
        $this->assertEquals($url->query(), [
            'query1' => 'value1',
            'query2' => 'value2',
        ]);

        $url->query(false);

        $this->assertEquals([], $url->query());
    }

    public function testPunycode()
    {
        $punycode = (new URLBuilder())->punycode();

        $this->assertEquals('xn--80ahntb6ao.xn--p1ai', $punycode->encode('духанин.рф'));
        $this->assertEquals('духанин.рф', $punycode->decode('xn--80ahntb6ao.xn--p1ai'));
    }

    public function testCopy()
    {
        $url1 = new URLBuilder('http://antondukhanin.ru/One/Two?query1=value1');

        $url2 = $url1->copy();

        $this->assertEquals($url2->compile(), $url1->compile());

        $url1->query(false);

        $this->assertNotEquals($url1->compile(), $url2->compile());
    }

    public function testToString()
    {
        $url = new URLBuilder('http://antondukhanin.ru/One/Two?query1=value1');

        $this->assertEquals('http://antondukhanin.ru/One/Two?query1=value1', $url->__toString());
    }
}