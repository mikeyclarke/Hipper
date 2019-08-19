<?php
declare(strict_types=1);

namespace Hipper\Tests\Document\Renderer\Mark;

use Hipper\Document\Renderer\HtmlFragmentRendererContext;
use Hipper\Document\Renderer\Mark\Emphasis;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class EmphasisTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private $context;
    private $emphasisMark;

    public function setUp(): void
    {
        $this->context = m::mock(HtmlFragmentRendererContext::class);
        $this->emphasisMark = new Emphasis(
            $this->context
        );
    }

    /**
     * @test
     */
    public function getHtmlTags()
    {
        $attributes = null;

        $expected = ['<em>', '</em>'];

        $result = $this->emphasisMark->getHtmlTags($attributes);
        $this->assertEquals($expected, $result);
    }
}
