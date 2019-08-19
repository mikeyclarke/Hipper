<?php
declare(strict_types=1);

namespace Hipper\Tests\Document\Renderer\Node;

use Hipper\Document\Renderer\HtmlFragmentRendererContext;
use Hipper\Document\Renderer\Node\Paragraph;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class ParagraphTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private $context;
    private $paragraphNode;

    public function setUp(): void
    {
        $this->context = m::mock(HtmlFragmentRendererContext::class);
        $this->paragraphNode = new Paragraph(
            $this->context
        );
    }

    /**
     * @test
     */
    public function getHtmlTags()
    {
        $attributes = null;

        $expected = ['<p>', '</p>'];

        $result = $this->paragraphNode->getHtmlTags($attributes);
        $this->assertEquals($expected, $result);
    }
}
