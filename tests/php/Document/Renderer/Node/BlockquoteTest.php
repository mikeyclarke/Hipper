<?php
declare(strict_types=1);

namespace Hipper\Tests\Document\Renderer\Node;

use Hipper\Document\Renderer\HtmlFragmentRendererContext;
use Hipper\Document\Renderer\Node\Blockquote;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class BlockquoteTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private $context;
    private $blockquoteNode;

    public function setUp(): void
    {
        $this->context = m::mock(HtmlFragmentRendererContext::class);
        $this->blockquoteNode = new Blockquote(
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

        $expected = ['<blockquote>', '</blockquote>'];

        $result = $this->blockquoteNode->getHtmlTags($attributes, $htmlId);
        $this->assertEquals($expected, $result);
    }
}
