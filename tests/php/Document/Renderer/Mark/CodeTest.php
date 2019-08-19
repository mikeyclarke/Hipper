<?php
declare(strict_types=1);

namespace Hipper\Tests\Document\Renderer\Mark;

use Hipper\Document\Renderer\HtmlFragmentRendererContext;
use Hipper\Document\Renderer\Mark\Code;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class CodeTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private $context;
    private $codeMark;

    public function setUp(): void
    {
        $this->context = m::mock(HtmlFragmentRendererContext::class);
        $this->codeMark = new Code(
            $this->context
        );
    }

    /**
     * @test
     */
    public function getHtmlTags()
    {
        $attributes = null;

        $expected = ['<code>', '</code>'];

        $result = $this->codeMark->getHtmlTags($attributes);
        $this->assertEquals($expected, $result);
    }
}
