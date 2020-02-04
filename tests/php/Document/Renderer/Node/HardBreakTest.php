<?php
declare(strict_types=1);

namespace Hipper\Tests\Document\Renderer\Node;

use Hipper\Document\Renderer\HtmlFragmentRendererContext;
use Hipper\Document\Renderer\Node\HardBreak;
use Hipper\Document\Renderer\Node\Paragraph;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class HardBreakTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private $context;
    private $hardBreakNode;

    public function setUp(): void
    {
        $this->context = m::mock(HtmlFragmentRendererContext::class);
        $this->hardBreakNode = new HardBreak(
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

        $expected = ['<br>'];

        $result = $this->hardBreakNode->getHtmlTags($attributes, $htmlId);
        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function toMarkdownString()
    {
        $content = '';

        $expected = "\n";

        $result = $this->hardBreakNode->toMarkdownString($content, 0, null, null);
        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function toMarkdownStringWhenFirstNodeInsideParagraphOutputsEmptyString()
    {
        $content = '';
        $index = 0;
        $parentNode = new Paragraph($this->context);

        $expected = '';

        $result = $this->hardBreakNode->toMarkdownString($content, $index, $parentNode, null);
        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function toMarkdownStringWhenNotFirstNodeInsideParagraphOutputsNewline()
    {
        $content = '';
        $index = 1;
        $parentNode = new Paragraph($this->context);

        $expected = "\n";

        $result = $this->hardBreakNode->toMarkdownString($content, $index, $parentNode, null);
        $this->assertEquals($expected, $result);
    }
}
