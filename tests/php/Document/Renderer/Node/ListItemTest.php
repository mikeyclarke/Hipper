<?php
declare(strict_types=1);

namespace Hipper\Tests\Document\Renderer\Node;

use Hipper\Document\Renderer\HtmlFragmentRendererContext;
use Hipper\Document\Renderer\Node\ListItem;
use Hipper\Document\Renderer\Node\OrderedList;
use Hipper\Document\Renderer\Node\UnorderedList;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class ListItemTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private $context;
    private $listItemNode;

    public function setUp(): void
    {
        $this->context = m::mock(HtmlFragmentRendererContext::class);
        $this->listItemNode = new ListItem(
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

        $expected = ['<li>', '</li>'];

        $result = $this->listItemNode->getHtmlTags($attributes, $htmlId);
        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function toPlainTextString()
    {
        $textContent = 'Some list item text';

        $expected = $textContent;

        $result = $this->listItemNode->toPlainTextString($textContent);
        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function toMarkdownStringWithOrderedList()
    {
        $content = 'Some list item text';
        $parentNode = new OrderedList($this->context);

        $expected = "3. Some list item text";

        $result = $this->listItemNode->toMarkdownString($content, 2, $parentNode, null);
        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function toMarkdownStringWithUnorderedList()
    {
        $content = 'Some list item text';
        $parentNode = new UnorderedList($this->context);

        $expected = "- Some list item text";

        $result = $this->listItemNode->toMarkdownString($content, 2, $parentNode, null);
        $this->assertEquals($expected, $result);
    }
}
