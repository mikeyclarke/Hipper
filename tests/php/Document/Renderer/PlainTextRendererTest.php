<?php
declare(strict_types=1);

namespace Hipper\Tests\Document\Renderer;

use Hipper\Document\Renderer\HtmlEscaper;
use Hipper\Document\Renderer\HtmlFragmentRendererContext;
use Hipper\Document\Renderer\HtmlFragmentRendererContextFactory;
use Hipper\Document\Renderer\Node\NodeFactory;
use Hipper\Document\Renderer\Node\Paragraph;
use Hipper\Document\Renderer\Node\Text;
use Hipper\Document\Renderer\PlainTextRenderer;
use Hipper\Document\Renderer\StringTerminator;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class PlainTextRendererTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private $nodeFactory;
    private $context;
    private $htmlEscaper;
    private $stringTerminator;

    public function setUp(): void
    {
        $this->nodeFactory = m::mock(NodeFactory::class);
        $this->context = m::mock(HtmlFragmentRendererContext::class);
        $this->htmlEscaper = m::mock(HtmlEscaper::class);
        $this->stringTerminator = m::mock(StringTerminator::class);
    }

    /**
     * @test
     */
    public function nodesWithoutATypeAreSkipped()
    {
        $doc = [
            'type' => 'doc',
            'content' => [
                [
                    'text' => 'Foo',
                ],
                [
                    'content' => [
                        'type' => 'horizontal_rule',
                    ],
                ],
            ],
        ];

        $renderer = new PlainTextRenderer(
            $this->nodeFactory,
            ['horizontal_rule']
        );

        $expected = '';

        $result = $renderer->render($doc, $this->context);
        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function disallowedNodesAreSkipped()
    {
        $doc = [
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'horizontal_rule',
                ],
            ],
        ];

        $renderer = new PlainTextRenderer(
            $this->nodeFactory,
            ['paragraph']
        );

        $expected = '';

        $result = $renderer->render($doc, $this->context);
        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function textNodeTextIsEscaped()
    {
        $doc = [
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'paragraph',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'Hi!!!',
                        ],
                    ],
                ],
            ],
        ];

        $renderer = new PlainTextRenderer(
            $this->nodeFactory,
            ['paragraph', 'text']
        );

        $this->createNodeFactoryExpectation(['paragraph', $this->context], new Paragraph($this->context));
        $this->createNodeFactoryExpectation(['text', $this->context], new Text($this->context));
        $this->createContextGetHtmlEscaperExpectation();
        $this->createHtmlEscaperExpectation(['Hi!!!'], 'Hi I’m escaped!!!');
        $this->createHtmlFragmentRendererContextExpectation();
        $this->createStringTerminatorExpectation(['Hi I’m escaped!!!'], 'Hi I’m escaped!!!');

        $expected = "Hi I’m escaped!!!\r\n";

        $result = $renderer->render($doc, $this->context);
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

    private function createHtmlEscaperExpectation($args, $result)
    {
        $this->htmlEscaper
            ->shouldReceive('escapeInnerText')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createContextGetHtmlEscaperExpectation()
    {
        $this->context
            ->shouldReceive('getHtmlEscaper')
            ->once()
            ->andReturn($this->htmlEscaper);
    }

    private function createNodeFactoryExpectation($args, $result)
    {
        $this->nodeFactory
            ->shouldReceive('create')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }
}
