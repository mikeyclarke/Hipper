<?php
declare(strict_types=1);

namespace Hipper\Tests\Document\Renderer\Node;

use Hipper\Document\Renderer\HtmlFragmentRendererContext;
use Hipper\Document\Renderer\Node\OrderedList;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class OrderedListTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private $orderedListNode;

    public function setUp(): void
    {
        $context = m::mock(HtmlFragmentRendererContext::class);
        $this->orderedListNode = new OrderedList($context);
    }

    /**
     * @test
     */
    public function defaultsToNoStartAttribute()
    {
        $expected = ['<ol>', '</ol>'];

        $result = $this->orderedListNode->getHtmlTags(null);
        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function startAttributeOutput()
    {
        $attributes = ['start' => 9];

        $expected = ['<ol start="9">', '</ol>'];

        $result = $this->orderedListNode->getHtmlTags($attributes);
        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function nonIntegerStartAttributeValueIsDiscarded()
    {
        $attributes = [
            'start' => '"></ol></script>window.location.assign("http://scammy-site.example.com");</script>'
        ];

        $expected = ['<ol>', '</ol>'];

        $result = $this->orderedListNode->getHtmlTags($attributes);
        $this->assertEquals($expected, $result);
    }
}