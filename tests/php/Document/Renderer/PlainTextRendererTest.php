<?php
declare(strict_types=1);

namespace Hipper\Tests\Document\Renderer;

use Hipper\Document\Renderer\HtmlEscaper;
use Hipper\Document\Renderer\HtmlFragmentRendererContext;
use Hipper\Document\Renderer\HtmlFragmentRendererContextFactory;
use Hipper\Document\Renderer\Node\Heading;
use Hipper\Document\Renderer\Node\ListItem;
use Hipper\Document\Renderer\Node\NodeFactory;
use Hipper\Document\Renderer\Node\OrderedList;
use Hipper\Document\Renderer\Node\Paragraph;
use Hipper\Document\Renderer\Node\Text;
use Hipper\Document\Renderer\PlainTextRenderer;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class PlainTextRendererTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private $nodeFactory;
    private $context;
    private $htmlEscaper;

    public function setUp(): void
    {
        $this->nodeFactory = m::mock(NodeFactory::class);
        $this->context = m::mock(HtmlFragmentRendererContext::class);
        $this->htmlEscaper = m::mock(HtmlEscaper::class);
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

        $expected = "Hi I’m escaped!!!\r\n\r\n";

        $result = $renderer->render($doc, $this->context);
        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function nodesAreCorrectlyConcatenated()
    {
        $doc = [
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'heading',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'A heading',
                        ],
                    ],
                ],
                [
                    'type' => 'paragraph',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'Some paragraph text.',
                        ],
                    ],
                ],
                [
                    'type' => 'paragraph',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'Some more paragraph text.',
                        ],
                    ],
                ],
                [
                    'type' => 'ordered_list',
                    'content' => [
                        [
                            'type' => 'list_item',
                            'content' => [
                                [
                                    'type' => 'paragraph',
                                    'content' => [
                                        [
                                            'type' => 'text',
                                            'text' => 'First list item.',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [
                            'type' => 'list_item',
                            'content' => [
                                [
                                    'type' => 'paragraph',
                                    'content' => [
                                        [
                                            'type' => 'text',
                                            'text' => 'Second list item.',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'type' => 'paragraph',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'Final paragraph text.',
                        ],
                    ],
                ],
            ],
        ];

        $renderer = new PlainTextRenderer(
            $this->nodeFactory,
            ['heading', 'paragraph', 'text', 'ordered_list', 'list_item']
        );

        $this->createNodeFactoryExpectation(['heading', $this->context], new Heading($this->context));
        $this->createNodeFactoryExpectation(['text', $this->context], new Text($this->context));
        $this->createContextGetHtmlEscaperExpectation();
        $this->createHtmlEscaperExpectation(['A heading'], 'A heading');

        $this->createNodeFactoryExpectation(['paragraph', $this->context], new Paragraph($this->context));
        $this->createNodeFactoryExpectation(['text', $this->context], new Text($this->context));
        $this->createContextGetHtmlEscaperExpectation();
        $this->createHtmlEscaperExpectation(['Some paragraph text.'], 'Some paragraph text.');

        $this->createNodeFactoryExpectation(['paragraph', $this->context], new Paragraph($this->context));
        $this->createNodeFactoryExpectation(['text', $this->context], new Text($this->context));
        $this->createContextGetHtmlEscaperExpectation();
        $this->createHtmlEscaperExpectation(['Some more paragraph text.'], 'Some more paragraph text.');

        $this->createNodeFactoryExpectation(['ordered_list', $this->context], new OrderedList($this->context));

        $this->createNodeFactoryExpectation(['list_item', $this->context], new ListItem($this->context));
        $this->createNodeFactoryExpectation(['paragraph', $this->context], new Paragraph($this->context));
        $this->createNodeFactoryExpectation(['text', $this->context], new Text($this->context));
        $this->createContextGetHtmlEscaperExpectation();
        $this->createHtmlEscaperExpectation(['First list item.'], 'First list item.');

        $this->createNodeFactoryExpectation(['list_item', $this->context], new ListItem($this->context));
        $this->createNodeFactoryExpectation(['paragraph', $this->context], new Paragraph($this->context));
        $this->createNodeFactoryExpectation(['text', $this->context], new Text($this->context));
        $this->createContextGetHtmlEscaperExpectation();
        $this->createHtmlEscaperExpectation(['Second list item.'], 'Second list item.');

        $this->createNodeFactoryExpectation(['paragraph', $this->context], new Paragraph($this->context));
        $this->createNodeFactoryExpectation(['text', $this->context], new Text($this->context));
        $this->createContextGetHtmlEscaperExpectation();
        $this->createHtmlEscaperExpectation(['Final paragraph text.'], 'Final paragraph text.');

        $expected = "A heading\r\n\r\n" .
            "Some paragraph text.\r\n\r\n" .
            "Some more paragraph text.\r\n\r\n" .
            "• First list item.\r\n\r\n\r\n\r\n" .
            "• Second list item.\r\n\r\n\r\n\r\n\r\n\r\n" .
            "Final paragraph text.\r\n\r\n";

        $result = $renderer->render($doc, $this->context);
        $this->assertEquals($expected, $result);
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
