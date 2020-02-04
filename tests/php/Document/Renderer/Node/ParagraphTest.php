<?php
declare(strict_types=1);

namespace Hipper\Tests\Document\Renderer\Node;

use Hipper\Document\Renderer\HtmlFragmentRendererContext;
use Hipper\Document\Renderer\Node\ListItem;
use Hipper\Document\Renderer\Node\Paragraph;
use Hipper\Document\Renderer\StringTerminator;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class ParagraphTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private $context;
    private $paragraphNode;
    private $stringTerminator;

    public function setUp(): void
    {
        $this->context = m::mock(HtmlFragmentRendererContext::class);
        $this->paragraphNode = new Paragraph(
            $this->context
        );

        $this->stringTerminator = m::mock(StringTerminator::class);
    }

    /**
     * @test
     */
    public function getHtmlTags()
    {
        $attributes = null;
        $htmlId = null;

        $expected = ['<p>', '</p>'];

        $result = $this->paragraphNode->getHtmlTags($attributes, $htmlId);
        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function toPlainTextString()
    {
        $textContent = 'Speaking English is exhausting';

        $terminated = 'Speaking English is exhausting.';

        $this->createHtmlFragmentRendererContextExpectation();
        $this->createStringTerminatorExpectation([$textContent], $terminated);

        $expected = $terminated . "\r\n";

        $result = $this->paragraphNode->toPlainTextString($textContent);
        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function toMarkdownString()
    {
        $content = 'Speaking English is exhausting';

        $expected = "Speaking English is exhausting\n\n";

        $result = $this->paragraphNode->toMarkdownString($content, 0, null, null);
        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function toMarkdownStringOmitsAdditionalTrailingNewlineIfInsideListItem()
    {
        $content = 'Speaking English is exhausting';
        $parentNode = new ListItem($this->context);

        $expected = "Speaking English is exhausting\n";

        $result = $this->paragraphNode->toMarkdownString($content, 0, $parentNode, null);
        $this->assertEquals($expected, $result);
    }

    private function createStringTerminatorExpectation($args, $result)
    {
        $this->stringTerminator
            ->shouldReceive('terminateStringWithPunctuationCharacter')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createHtmlFragmentRendererContextExpectation()
    {
        $this->context
            ->shouldReceive('getStringTerminator')
            ->once()
            ->andReturn($this->stringTerminator);
    }
}
