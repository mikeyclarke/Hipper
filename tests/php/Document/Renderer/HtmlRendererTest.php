<?php
declare(strict_types=1);

namespace Hipper\Tests\Document\Renderer;

use Hipper\Document\Renderer\HtmlEscaper;
use Hipper\Document\Renderer\HtmlFragmentRendererContext;
use Hipper\Document\Renderer\HtmlFragmentRendererContextFactory;
use Hipper\Document\Renderer\HtmlRenderer;
use Hipper\Document\Renderer\Mark\Emphasis;
use Hipper\Document\Renderer\Mark\MarkFactory;
use Hipper\Document\Renderer\Node\HardBreak;
use Hipper\Document\Renderer\Node\HorizontalRule;
use Hipper\Document\Renderer\Node\Image;
use Hipper\Document\Renderer\Node\NodeFactory;
use Hipper\Document\Renderer\Node\Paragraph;
use Hipper\Document\Renderer\Node\Text;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class HtmlRendererTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private $markFactory;
    private $nodeFactory;
    private $context;
    private $htmlEscaper;

    public function setUp(): void
    {
        $this->markFactory = m::mock(MarkFactory::class);
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

        $htmlRenderer = new HtmlRenderer(
            $this->markFactory,
            $this->nodeFactory,
            [],
            ['horizontal_rule']
        );

        $expected = '';

        $result = $htmlRenderer->render($doc, $this->context);
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

        $htmlRenderer = new HtmlRenderer(
            $this->markFactory,
            $this->nodeFactory,
            [],
            ['paragraph']
        );

        $expected = '';

        $result = $htmlRenderer->render($doc, $this->context);
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

        $htmlRenderer = new HtmlRenderer(
            $this->markFactory,
            $this->nodeFactory,
            [],
            ['paragraph', 'text']
        );

        $this->createNodeFactoryExpectation(['paragraph', $this->context], new Paragraph($this->context));
        $this->createNodeFactoryExpectation(['text', $this->context], new Text($this->context));
        $this->createContextGetHtmlEscaperExpectation();
        $this->createHtmlEscaperExpectation(['Hi!!!'], 'Hi I’m escaped!!!');

        $expected = '<p>Hi I’m escaped!!!</p>';

        $result = $htmlRenderer->render($doc, $this->context);
        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function nodesThatReturnNullHtmlTagsAreSkipped()
    {
        // Image node returns null for HTML tags if no attributes with a valid `src`
        $doc = [
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'image',
                ],
                [
                    'type' => 'horizontal_rule',
                ],
            ],
        ];

        $htmlRenderer = new HtmlRenderer(
            $this->markFactory,
            $this->nodeFactory,
            [],
            ['image', 'horizontal_rule']
        );

        $this->createNodeFactoryExpectation(['image', $this->context], new Image($this->context));
        $this->createNodeFactoryExpectation(['horizontal_rule', $this->context], new HorizontalRule($this->context));

        $expected = '<hr>';

        $result = $htmlRenderer->render($doc, $this->context);
        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function emptyParagraphsDynamicallyAddAHardBreakChildNode()
    {
        $doc = [
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'paragraph',
                ],
            ],
        ];

        $htmlRenderer = new HtmlRenderer(
            $this->markFactory,
            $this->nodeFactory,
            [],
            ['paragraph', 'hard_break']
        );

        $this->createNodeFactoryExpectation(['paragraph', $this->context], new Paragraph($this->context));
        $this->createNodeFactoryExpectation(['hard_break', $this->context], new HardBreak($this->context));

        $expected = '<p><br></p>';

        $result = $htmlRenderer->render($doc, $this->context);
        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function disallowedMarksAreSkipped()
    {
        $doc = [
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'paragraph',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'We are ',
                        ],
                        [
                            'type' => 'text',
                            'text' => 'bold',
                            'marks' => [
                                [
                                    'type' => 'strong',
                                ]
                            ],
                        ],
                        [
                            'type' => 'text',
                            'text' => ' and ',
                        ],
                        [
                            'type' => 'text',
                            'text' => 'italic',
                            'marks' => [
                                [
                                    'type' => 'emphasis',
                                ]
                            ],
                        ],
                        [
                            'type' => 'text',
                            'text' => '.',
                        ],
                    ],
                ],
            ],
        ];

        $htmlRenderer = new HtmlRenderer(
            $this->markFactory,
            $this->nodeFactory,
            ['emphasis'],
            ['paragraph', 'text']
        );

        $this->createNodeFactoryExpectation(['paragraph', $this->context], new Paragraph($this->context));
        $this->createNodeFactoryExpectation(['text', $this->context], new Text($this->context));
        $this->createContextGetHtmlEscaperExpectation();
        $this->createHtmlEscaperExpectation(['We are '], 'We are ');
        $this->createNodeFactoryExpectation(['text', $this->context], new Text($this->context));
        $this->createContextGetHtmlEscaperExpectation();
        $this->createHtmlEscaperExpectation(['bold'], 'bold');
        $this->createNodeFactoryExpectation(['text', $this->context], new Text($this->context));
        $this->createContextGetHtmlEscaperExpectation();
        $this->createHtmlEscaperExpectation([' and '], ' and ');
        $this->createNodeFactoryExpectation(['text', $this->context], new Text($this->context));
        $this->createContextGetHtmlEscaperExpectation();
        $this->createHtmlEscaperExpectation(['italic'], 'italic');
        $this->createMarkFactoryExpectation(['emphasis', $this->context], new Emphasis($this->context));
        $this->createNodeFactoryExpectation(['text', $this->context], new Text($this->context));
        $this->createContextGetHtmlEscaperExpectation();
        $this->createHtmlEscaperExpectation(['.'], '.');

        $expected = '<p>We are bold and <em>italic</em>.</p>';

        $result = $htmlRenderer->render($doc, $this->context);
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

    private function createMarkFactoryExpectation($args, $result)
    {
        $this->markFactory
            ->shouldReceive('create')
            ->once()
            ->with(...$args)
            ->andReturn($result);
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
