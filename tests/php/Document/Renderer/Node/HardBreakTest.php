<?php
declare(strict_types=1);

namespace Hipper\Tests\Document\Renderer\Node;

use Hipper\Document\Renderer\HtmlFragmentRendererContext;
use Hipper\Document\Renderer\Node\HardBreak;
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

        $expected = ['<br>'];

        $result = $this->hardBreakNode->getHtmlTags($attributes);
        $this->assertEquals($expected, $result);
    }
}
