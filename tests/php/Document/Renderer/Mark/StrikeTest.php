<?php
declare(strict_types=1);

namespace Hipper\Tests\Document\Renderer\Mark;

use Hipper\Document\Renderer\HtmlFragmentRendererContext;
use Hipper\Document\Renderer\Mark\Strike;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class StrikeTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private $context;
    private $strikeMark;

    public function setUp(): void
    {
        $this->context = m::mock(HtmlFragmentRendererContext::class);
        $this->strikeMark = new Strike(
            $this->context
        );
    }

    /**
     * @test
     */
    public function getHtmlTags()
    {
        $attributes = null;

        $expected = ['<s>', '</s>'];

        $result = $this->strikeMark->getHtmlTags($attributes);
        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function toMarkdownString()
    {
        $text = 'Some text';
        $attributes = null;

        $expected = '~~Some text~~';

        $result = $this->strikeMark->toMarkdownString($text, $attributes);
        $this->assertEquals($expected, $result);
    }
}
