<?php
namespace Dukhanin\Support\Tests;

use PHPUnit\Framework\Constraint\IsType;
use Dukhanin\Support\HTMLGenerator;

class HTMLGeneratorTest extends TestCase
{
    /**
     * @var \Dukhanin\Support\HTMLGenerator
     */
    protected $generator;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    public function setUp()
    {
        $this->generator = new HTMLGenerator;
    }

    public function testOpenTag()
    {
        $this->assertEquals('<a>', $this->generator->openTag('a'));
    }

    public function testCloseTag()
    {
        $this->assertEquals('</a>', $this->generator->closeTag('a'));
    }

    public function testRenderTag()
    {
        $this->assertEquals('<a />', $this->generator->renderTag('a'));
        $this->assertEquals('<a></a>', $this->generator->renderTag('a', ['tag-plural' => true]));
        $this->assertEquals('<a />', $this->generator->renderTag('a', ['tag-singular' => true]));

        $this->assertEquals('<a>hello</a>', $this->generator->renderTag('a', ['content' => 'hello']));
        $this->assertEquals('<a />', $this->generator->renderTag('a', ['content' => 'hello', 'tag-singular' => true]));
        $this->assertEquals('<a>hello</a>',
            $this->generator->renderTag('a', ['content' => 'hello', 'tag-plural' => true]));
    }

    public function testRenderAttributes()
    {
        $this->assertEquals('', $this->generator->renderAttributes([]));
        $this->assertEquals("width='1'", $this->generator->renderAttributes(['width' => 1]));
    }

    public function testAppend()
    {
        $tag = ['tag-name' => 'a', 'content' => 'hello'];

        $this->generator->append($tag, ' world');

        $this->assertEquals($this->generator->renderTag($tag), '<a>hello world</a>');
    }

    public function testPrepend()
    {
        $tag = ['tag-name' => 'a', 'content' => 'world'];

        $this->generator->prepend($tag, 'hello ');

        $this->assertEquals('<a>hello world</a>', $this->generator->renderTag($tag));
    }

    public function testMerge()
    {
        $tag = ['tag-name' => 'a', 'content' => 'hello world'];

        $tag = $this->generator->merge($tag, ['class' => 'highlight']);

        $this->assertEquals("<a class='highlight'>hello world</a>", $this->generator->renderTag($tag));
    }

    public function testAddClass()
    {
        $tag = [
            'tag-name' => 'a',
            'class' => 'hello',
        ];

        $this->generator->addClass($tag, 'world');

        $this->assertEquals('hello world', $tag['class']);
    }

    public function testValidateTag()
    {
        $tag = null;
        $this->generator->validateTag($tag);
        $this->assertArraySubset(['tag-name' => null, 'content' => null], $tag);

        $tag = '';
        $this->generator->validateTag($tag);
        $this->assertArraySubset(['tag-name' => null, 'content' => null], $tag);

        $tag = ['hello.world' => true];
        $this->generator->validateTag($tag);
        $this->assertArraySubset(['tag-name' => null, 'content' => null, 'hello' => []], $tag);
    }

    public function testValidateTagName()
    {
        $tagName = null;
        $this->generator->validateTagName($tagName);
        $this->assertEquals('span', $tagName);

        $tagName = '';
        $this->generator->validateTagName($tagName);
        $this->assertEquals('span', $tagName);

        $tagName = 'a';
        $this->generator->validateTagName($tagName);
        $this->assertEquals('a', $tagName);
    }

    public function testValidateClass()
    {
        $class = null;
        $this->generator->validateClass($class);
        $this->assertNull($class);

        $class = '';
        $this->generator->validateClass($class);
        $this->assertNull($class);

        $class = [];
        $this->generator->validateClass($class);
        $this->assertNull($class);

        $class = 'hello world';
        $this->generator->validateClass($class);
        $this->assertEquals('hello world', $class);

        $class = [null, 'hello', 'world', 'world'];
        $this->generator->validateClass($class);
        $this->assertEquals('hello world', $class);
    }

    public function testValidateAttributes()
    {
        foreach ([null, '', []] as $attributes) {
            $this->generator->validateAttributes($attributes);
            $this->assertInternalType(IsType::TYPE_ARRAY, $attributes);
        }

        $attributes = ['class' => ['hello', 'world']];
        $this->generator->validateAttributes($attributes);
        $this->assertEquals(['class' => 'hello world'], $attributes);
    }

    public function testValidateContent()
    {
        $content = '';
        $this->generator->validateContent($content);
        $this->assertEquals('', $content);

        $content = ['tag-name' => 'a', 'content' => 'hello world', 'class' => ['hello', 'world']];
        $this->generator->validateContent($content);
        $this->assertEquals("<a class='hello world'>hello world</a>", $content);
    }

    public function testPreprocessTitle()
    {
        $this->assertEquals("<span title='hello world'>hello world</span>",
            $this->generator->renderTag(['label' => 'hello world']));
        $this->assertEquals("<span title='hello world'>no way</span>",
            $this->generator->renderTag(['label' => 'hello world', 'content' => 'no way']));
        $this->assertEquals("<span title=''>hello world</span>",
            $this->generator->renderTag(['label' => 'hello world', 'title' => '']));
    }

    public function testPreprocessIcon()
    {
        $this->assertEquals("<span> <i class='fa fa-clock'></i> hello world</span>", $this->generator->renderTag([
            'icon' => 'fa fa-clock',
            'content' => 'hello world',
        ]));

        $this->assertEquals("<span> <i class='fa fa-clock'></i> </span>", $this->generator->renderTag([
            'icon' => 'fa fa-clock',
            'icon-only' => true,
            'content' => 'hello world',
        ]));

        $this->assertEquals("<span title='hello world' data-toggle='tooltip' data-placement='auto'> <i class='fa fa-clock'></i> </span>",
            $this->generator->renderTag([
                'icon' => 'fa fa-clock',
                'icon-only' => true,
                'content' => 'hello world',
                'title' => 'hello world',
            ]));
    }

    public function testPreprocessUrl()
    {
        $this->assertEquals("<span url='http://helloworld.ru' />", $this->generator->renderTag([
            'tag-name' => 'span',
            'url' => 'http://helloworld.ru',
        ]));

        $this->assertEquals("<a href='http://helloworld.ru' />", $this->generator->renderTag([
            'tag-name' => 'a',
            'url' => 'http://helloworld.ru',
        ]));

        $this->assertEquals("<a url='http://helloworld.ru' href='/helloworld' />", $this->generator->renderTag([
            'tag-name' => 'a',
            'url' => 'http://helloworld.ru',
            'href' => '/helloworld',
        ]));

        $this->assertEquals("<a href='http://helloworld.ru' />", $this->generator->renderTag([
            'url' => 'http://helloworld.ru',
        ]));
    }

    public function testAttributes()
    {
        $this->assertEquals("<span data-one='one' data-two='two' />", $this->generator->renderTag([
            'data-one' => 'one',
            'data' => ['two' => 'two',],
        ]));
    }

    public function testStringToTag()
    {
        $this->assertEquals('<span />', $this->generator->renderTag('span'));
        $this->assertEquals('<a />', $this->generator->renderTag('a'));
        $this->assertEquals("<a id='id' />", $this->generator->renderTag('a#id'));
        $this->assertEquals("<a class='hello' />", $this->generator->renderTag('a.hello'));
    }
}