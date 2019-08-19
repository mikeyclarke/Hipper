<?php
declare(strict_types=1);

namespace Hipper\Tests\Document\Renderer\Node;

use Hipper\Document\Renderer\HtmlFragmentRendererContext;
use Hipper\Document\Renderer\Node\HorizontalRule;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class HorizontalRuleTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private $context;
    private $horizontalRuleNode;

    public function setUp(): void
    {
        $this->context = m::mock(HtmlFragmentRendererContext::class);
        $this->horizontalRuleNode = new HorizontalRule(
            $this->context
        );
    }

    /**
     * @test
     */
    public function getHtmlTags()
    {
        $attributes = null;

        $expected = ['<hr>'];

        $result = $this->horizontalRuleNode->getHtmlTags($attributes);
        $this->assertEquals($expected, $result);
    }
}
