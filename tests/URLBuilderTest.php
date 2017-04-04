<?php

namespace Tests\Unit;

use Dukhanin\Support\URLBuilder;
use PHPUnit\Framework\TestCase;

class URLBuilderTest extends TestCase
{
    public function testCompile()
    {
        $url = new URLBuilder('http://dukhanin:testPass@antondukhanin.ru/One/Two?query1=value1&query2=value2#fragment');
        $this->assertTrue($url->compile() === 'http://dukhanin:testPass@antondukhanin.ru/One/Two?query1=value1&query2=value2#fragment');

        $url = new URLBuilder();
        $url->scheme('http')->user('dukhanin')->pass('testPass')->host('antondukhanin.ru')->path('One/Two')->query([
            'query1' => 'value1',
            'query2' => 'value2',
        ])->fragment('fragment');

        $this->assertTrue($url->compile() === 'http://dukhanin:testPass@antondukhanin.ru/One/Two?query1=value1&query2=value2#fragment');
    }

    public function testCaseSensitive()
    {
        $url = new URLBuilder;

        $url->caseSensitive(true);
        $this->assertTrue($url->caseSensitive() === true);

        $url->caseSensitive(false);
        $this->assertTrue($url->caseSensitive() === false);
    }

    public function testEncoded()
    {
        $url1 = new URLBuilder('http://духанин.рф/Один/Два?query=value#fragment');
        $url2 = new URLBuilder('http://xn--80ahntb6ao.xn--p1ai/%D0%9E%D0%B4%D0%B8%D0%BD/%D0%94%D0%B2%D0%B0?query=value#fragment');

        $url1->encoded(false);
        $url2->encoded(false);

        $this->assertTrue($url1->encoded() === false);
        $this->assertTrue($url2->encoded() === false);

        $this->assertTrue($url1->host() === 'духанин.рф');
        $this->assertTrue($url2->host() === 'духанин.рф');

        $this->assertTrue($url1->path() === 'Один/Два');
        $this->assertTrue($url2->path() === 'Один/Два');

        $this->assertTrue($url1->compile() === 'http://духанин.рф/Один/Два?query=value#fragment');
        $this->assertTrue($url2->compile() === 'http://духанин.рф/Один/Два?query=value#fragment');

        $url1->encoded(true);
        $url2->encoded(true);

        $this->assertTrue($url1->encoded() === true);
        $this->assertTrue($url2->encoded() === true);

        $this->assertTrue($url1->host() === 'xn--80ahntb6ao.xn--p1ai');
        $this->assertTrue($url2->host() === 'xn--80ahntb6ao.xn--p1ai');

        $this->assertTrue($url1->path() === '%D0%9E%D0%B4%D0%B8%D0%BD/%D0%94%D0%B2%D0%B0');
        $this->assertTrue($url2->path() === '%D0%9E%D0%B4%D0%B8%D0%BD/%D0%94%D0%B2%D0%B0');

        $this->assertTrue($url1->compile() === 'http://xn--80ahntb6ao.xn--p1ai/%D0%9E%D0%B4%D0%B8%D0%BD/%D0%94%D0%B2%D0%B0?query=value#fragment');
        $this->assertTrue($url2->compile() === 'http://xn--80ahntb6ao.xn--p1ai/%D0%9E%D0%B4%D0%B8%D0%BD/%D0%94%D0%B2%D0%B0?query=value#fragment');
    }

    public function testScheme()
    {
        $url = new URLBuilder('HTTP://antondukhanin.ru/One/Two?query=value#fragment');

        $this->assertTrue($url->scheme() === 'http');

        $url->scheme('HTTPS://');

        $this->assertTrue($url->scheme() === 'https');
    }

    public function testFragment()
    {
        $url = new URLBuilder('http://antondukhanin.ru/One/Two?query=value');

        $this->assertTrue($url->fragment() === null);

        $url->fragment('#fragment');

        $this->assertTrue($url->fragment() === 'fragment');
        $this->assertTrue($url->compile() === 'http://antondukhanin.ru/One/Two?query=value#fragment');
    }

    public function testSecure()
    {
        $url = new URLBuilder('http://antondukhanin.ru/One/Two?query=value#fragment');

        $this->assertTrue($url->secure() === false);
        $this->assertTrue($url->scheme() === 'http');

        $url->secure(true);
        $this->assertTrue($url->secure() === true);
        $this->assertTrue($url->scheme() === 'https');
    }

    public function testUser()
    {
        $url = new URLBuilder('http://antondukhanin.ru/One/Two?query=value#fragment');

        $url->user('dukhanin');
        $this->assertTrue($url->user() === 'dukhanin');
        $this->assertTrue($url->compile() === 'http://dukhanin@antondukhanin.ru/One/Two?query=value#fragment');
    }

    public function testPass()
    {
        $url = new URLBuilder('http://antondukhanin.ru/One/Two?query=value#fragment');

        $url->user('dukhanin');
        $url->pass('testPass');
        $this->assertTrue($url->pass() === 'testPass');
        $this->assertTrue($url->compile() === 'http://dukhanin:testPass@antondukhanin.ru/One/Two?query=value#fragment');
    }

    public function testPath()
    {
        $url = new URLBuilder('http://antondukhanin.ru/One/Two?query=value#fragment');

        $this->assertTrue($url->path() === 'One/Two');

        $url->path(false);
        $this->assertTrue($url->path() === '');
        $this->assertTrue($url->compile() === 'http://antondukhanin.ru?query=value#fragment');

        $url->path('/');
        $this->assertTrue($url->path() === '');
        $this->assertTrue($url->compile() === 'http://antondukhanin.ru?query=value#fragment');

        $url->path('/One/Two/');
        $this->assertTrue($url->path() === 'One/Two');
        $this->assertTrue($url->compile() === 'http://antondukhanin.ru/One/Two?query=value#fragment');

        $url->path(['One', 'Two', 'three']);
        $this->assertTrue($url->path() === 'One/Two/three');
        $this->assertTrue($url->compile() === 'http://antondukhanin.ru/One/Two/three?query=value#fragment');

        return $this;
    }

    public function testSegments()
    {
        $url = new URLBuilder('http://antondukhanin.ru/One/Two?query=value#fragment');

        $this->assertTrue($url->segments() === ['One', 'Two']);
    }

    public function testSegment()
    {
        $url = new URLBuilder('http://antondukhanin.ru/One/Two?query=value#fragment');

        $this->assertTrue($url->segment(0) === 'One');
        $this->assertTrue($url->segment(1) === 'Two');
        $this->assertTrue($url->segment(2) === false);
    }

    public function testAppend()
    {
        $url = new URLBuilder('http://antondukhanin.ru?query=value#fragment');

        $url->append('/One//Two/');
        $this->assertTrue($url->compile() === 'http://antondukhanin.ru/One/Two?query=value#fragment');

        $url->append('/Three/');
        $this->assertTrue($url->compile() === 'http://antondukhanin.ru/One/Two/Three?query=value#fragment');

        $url->append(['/Four', 'Five']);
        $this->assertTrue($url->compile() === 'http://antondukhanin.ru/One/Two/Three/Four/Five?query=value#fragment');
    }

    public function testPrepend()
    {
        $url = new URLBuilder('http://antondukhanin.ru?query=value#fragment');

        $url->prepend('/Three//Four/');
        $this->assertTrue($url->compile() === 'http://antondukhanin.ru/Three/Four?query=value#fragment');

        $url->prepend(['One/', '/Two/']);
        $this->assertTrue($url->compile() === 'http://antondukhanin.ru/One/Two/Three/Four?query=value#fragment');
    }

    public function testShift()
    {
        // checking common cases, other specific cases
        // are represented in testShiftPath() and testShiftSegment() methods

        $url = new URLBuilder('http://antondukhanin.ru/One/Two/Three/Four/Five?query=value#fragment');

        // fetching incorrect path begining
        $this->assertTrue($url->shift('Ten/Eleven') === false);

        // fetching first two path segments
        $this->assertTrue($url->shift('One/Two') === 'One/Two');
        $this->assertTrue($url->compile() === 'http://antondukhanin.ru/Three/Four/Five?query=value#fragment');

        // fetching the first segment (index=0 by default)
        $this->assertTrue($url->shift() === 'Three');
        $this->assertTrue($url->compile() === 'http://antondukhanin.ru/Four/Five?query=value#fragment');
    }

    public function testShiftPath()
    {
        $url = new URLBuilder('http://antondukhanin.ru/One/Two/Three/Four/Five?query=value#fragment');

        // fetching incorrect path begining
        $this->assertTrue($url->shiftPath('Ten/Eleven') === false);

        // fetching first two path segments
        $this->assertTrue($url->shiftPath('/One//Two/') === 'One/Two');
        $this->assertTrue($url->compile() === 'http://antondukhanin.ru/Three/Four/Five?query=value#fragment');

        // fetching first two path segments using an array as argument
        $this->assertTrue($url->shiftPath(['Three', 'Four']) === 'Three/Four');
        $this->assertTrue($url->compile() === 'http://antondukhanin.ru/Five?query=value#fragment');

        // testing caseSensitive mode
        $url = new URLBuilder('http://antondukhanin.ru/One/Two/Three/Four/Five?query=value#fragment');

        $url->caseSensitive(true);

        $this->assertTrue($url->shiftPath('/one//two/') === false);
        $this->assertTrue($url->shiftPath('/One//Two/') === 'One/Two');
        $this->assertTrue($url->shiftPath(['three', 'four']) === false);
        $this->assertTrue($url->shiftPath(['Three', 'Four']) === 'Three/Four');

        // testing not caseSensitive mode
        $url = new URLBuilder('http://antondukhanin.ru/One/Two/Three/Four/Five?query=value#fragment');

        $url->caseSensitive(false);

        $this->assertTrue($url->shiftPath('/one//two/') === 'One/Two');
        $this->assertTrue($url->shiftPath(['three', 'four']) === 'Three/Four');
    }

    public function testShiftSegment()
    {
        $url = new URLBuilder('http://antondukhanin.ru/One/Two?query=value#fragment');

        $this->assertTrue($url->shiftSegment() === 'One');
        $this->assertTrue($url->compile() === 'http://antondukhanin.ru/Two?query=value#fragment');
    }

    public function testPop()
    {
        // checking common cases, other specific cases
        // are represented in testShiftPath() and testShiftSegment() methods

        $url = new URLBuilder('http://antondukhanin.ru/One/Two/Three/Four/Five?query=value#fragment');

        // fetching incorrect path ending
        $this->assertTrue($url->pop('Ten/Eleven') === false);

        // fetching first two path segments
        $this->assertTrue($url->pop('Four/Five') === 'Four/Five');
        $this->assertTrue($url->compile() === 'http://antondukhanin.ru/One/Two/Three?query=value#fragment');

        // fetching the last segment
        $this->assertTrue($url->pop() === 'Three');
        $this->assertTrue($url->compile() === 'http://antondukhanin.ru/One/Two?query=value#fragment');
    }

    public function testPopPath()
    {
        $url = new URLBuilder('http://antondukhanin.ru/One/Two/Three/Four/Five?query=value#fragment');

        // fetching incorrect path begining
        $this->assertTrue($url->popPath('Ten/Eleven') === false);

        // fetching first two path segments
        $this->assertTrue($url->popPath('/Four//Five/') === 'Four/Five');
        $this->assertTrue($url->compile() === 'http://antondukhanin.ru/One/Two/Three?query=value#fragment');

        // fetching first two path segments using an array as argument
        $this->assertTrue($url->popPath(['Two', 'Three']) === 'Two/Three');
        $this->assertTrue($url->compile() === 'http://antondukhanin.ru/One?query=value#fragment');

        // testing caseSensitive mode
        $url = new URLBuilder('http://antondukhanin.ru/One/Two/Three/Four/Five?query=value#fragment');

        $url->caseSensitive(true);

        $this->assertTrue($url->popPath('/four//five/') === false);
        $this->assertTrue($url->popPath('/Four//Five/') === 'Four/Five');
        $this->assertTrue($url->popPath(['two', 'three']) === false);
        $this->assertTrue($url->popPath(['Two', 'Three']) === 'Two/Three');

        // testing not caseSensitive mode
        $url = new URLBuilder('http://antondukhanin.ru/One/Two/Three/Four/Five?query=value#fragment');

        $url->caseSensitive(false);

        $this->assertTrue($url->popPath('/four//five/') === 'Four/Five');
        $this->assertTrue($url->popPath(['two', 'three']) === 'Two/Three');
    }

    public function testPopSegment()
    {
        $url = new URLBuilder('http://antondukhanin.ru/One/Two?query=value#fragment');

        $this->assertTrue($url->popSegment() === 'Two');
        $this->assertTrue($url->compile() === 'http://antondukhanin.ru/One?query=value#fragment');
    }

    public function testQueryString()
    {
        $url = new URLBuilder('http://antondukhanin.ru/One/Two?query1=value1&query2=value2#fragment');

        $this->assertTrue($url->queryString() === 'query1=value1&query2=value2');

        $url = new URLBuilder('http://antondukhanin.ru/One/Two#fragment');

        $this->assertTrue($url->queryString() === '');
    }

    public function testClearQuery()
    {
        $url = new URLBuilder('http://antondukhanin.ru/One/Two?query1=value1&query2=value2#fragment');

        $this->assertTrue($url->queryString() === 'query1=value1&query2=value2');

        $url->clearQuery();

        $this->assertTrue($url->queryString() === '');
        $this->assertTrue($url->compile() === 'http://antondukhanin.ru/One/Two#fragment');
    }

    public function testQuery()
    {
        $url = new URLBuilder('http://antondukhanin.ru/One/Two?query1=value1');

        $url->query(['query2' => 'value2']);

        $this->assertTrue($url->query('query1') === 'value1');
        $this->assertTrue($url->query('query2') === 'value2');
        $this->assertTrue($url->query() === [
                'query1' => 'value1',
                'query2' => 'value2',
            ]);

        $url->query(false);

        $this->assertTrue($url->query() === []);
    }

    public function testPunycode()
    {
        $punycode = (new URLBuilder())->punycode();

        $this->assertTrue($punycode->encode('духанин.рф') === 'xn--80ahntb6ao.xn--p1ai');
        $this->assertTrue($punycode->decode('xn--80ahntb6ao.xn--p1ai') === 'духанин.рф');
    }

    public function testCopy()
    {
        $url1 = new URLBuilder('http://antondukhanin.ru/One/Two?query1=value1');

        $url2 = $url1->copy();

        $this->assertTrue($url1->compile() === $url2->compile());

        $url1->query(false);

        $this->assertTrue($url1->compile() !== $url2->compile());
    }

    public function testToString()
    {
        $url = new URLBuilder('http://antondukhanin.ru/One/Two?query1=value1');

        $this->assertTrue($url->__toString() === 'http://antondukhanin.ru/One/Two?query1=value1');
    }
}