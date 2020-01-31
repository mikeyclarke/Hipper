<?php
declare(strict_types=1);

namespace Hipper\Tests\Document\Renderer\Node;

use Hipper\Document\Renderer\HtmlFragmentRendererContext;
use Hipper\Document\Renderer\Node\ListItem;
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

    /**
     * @test
     */
    public function toPlainTextString()
    {
        $textContent = "List item one.\r\nList item two.\r\nList item three.\r\nList item four.\r\n";

        $expected = $textContent;

        $result = $this->unorderedListNode->toPlainTextString($textContent);
        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function toMarkdownString()
    {
        $content = "- List item one.\n- List item two.\n- List item three.\n- List item four.\n";

        $expected = "- List item one.\n- List item two.\n- List item three.\n- List item four.\n\n";

        $result = $this->unorderedListNode->toMarkdownString($content, 0, null, null);
        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function toMarkdownStringWithListItemParent()
    {
        $content = "- List item one.\n- List item two.\n- List item three.\n- List item four.\n";
        $parentNode = new ListItem($this->context);

        $expected =
            "\n    - List item one.\n    - List item two.\n    - List item three.\n    - List item four.\n    \n\n";

        $result = $this->unorderedListNode->toMarkdownString($content, 0, $parentNode, null);
        $this->assertEquals($expected, $result);
    }
}
