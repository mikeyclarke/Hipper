<?php
declare(strict_types=1);

namespace Hipper\Tests\Document\Renderer\Mark;

use Hipper\Document\Renderer\HtmlFragmentRendererContext;
use Hipper\Document\Renderer\Mark\Strong;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class StrongTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private $context;
    private $strongMark;

    public function setUp(): void
    {
        $this->context = m::mock(HtmlFragmentRendererContext::class);
        $this->strongMark = new Strong(
            $this->context
        );
    }

    /**
     * @test
     */
    public function getHtmlTags()
    {
        $attributes = null;

        $expected = ['<strong>', '</strong>'];

        $result = $this->strongMark->getHtmlTags($attributes);
        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function toMarkdownString()
    {
        $text = 'Some text';
        $attributes = null;

        $expected = '**Some text**';

        $result = $this->strongMark->toMarkdownString($text, $attributes);
        $this->assertEquals($expected, $result);
    }
}
