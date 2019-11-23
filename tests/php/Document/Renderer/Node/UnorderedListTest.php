<?php
declare(strict_types=1);

namespace Hipper\Tests\Document\Renderer\Node;

use Hipper\Document\Renderer\HtmlFragmentRendererContext;
use Hipper\Document\Renderer\Node\UnorderedList;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class UnorderedListTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private $context;
    private $unorderedListNode;

    public function setUp(): void
    {
        $this->context = m::mock(HtmlFragmentRendererContext::class);
        $this->unorderedListNode = new UnorderedList(
            $this->context
        );
    }

    /**
     * @test
     */
    public function getHtmlTags()
    {
        $attributes = null;
        $htmlId = null;

        $expected = ['<ul>', '</ul>'];

        $result = $this->unorderedListNode->getHtmlTags($attributes, $htmlId);
        $this->assertEquals($expected, $result);
    }
}
