<?php
declare(strict_types=1);

namespace Hipper\Tests\Document\Renderer\Node;

use Hipper\Document\Renderer\HtmlFragmentRendererContext;
use Hipper\Document\Renderer\Node\ListItem;
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
}
