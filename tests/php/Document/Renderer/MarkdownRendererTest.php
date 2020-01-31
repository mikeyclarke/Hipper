<?php
declare(strict_types=1);

namespace Hipper\Tests\Document\Renderer;

use Hipper\Document\Renderer\HtmlFragmentRendererContext;
use Hipper\Document\Renderer\HtmlFragmentRendererContextFactory;
use Hipper\Document\Renderer\Mark\Code;
use Hipper\Document\Renderer\Mark\Emphasis;
use Hipper\Document\Renderer\Mark\MarkFactory;
use Hipper\Document\Renderer\MarkdownEscaper;
use Hipper\Document\Renderer\MarkdownRenderer;
use Hipper\Document\Renderer\Node\CodeBlock;
use Hipper\Document\Renderer\Node\HardBreak;
use Hipper\Document\Renderer\Node\NodeFactory;
use Hipper\Document\Renderer\Node\Paragraph;
use Hipper\Document\Renderer\Node\Text;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class MarkdownRendererTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private $markFactory;
    private $nodeFactory;
    private $context;
    private $markdownEscaper;

    public function setUp(): void
    {
        $this->markFactory = m::mock(MarkFactory::class);
        $this->nodeFactory = m::mock(NodeFactory::class);
        $this->context = m::mock(HtmlFragmentRendererContext::class);
        $this->markdownEscaper = m::mock(MarkdownEscaper::class);
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

        $markdownRenderer = new MarkdownRenderer(
            $this->markFactory,
            $this->nodeFactory,
            [],
            ['horizontal_rule']
        );

        $expected = '';

        $result = $markdownRenderer->render($doc, $this->context);
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

        $markdownRenderer = new MarkdownRenderer(
            $this->markFactory,
            $this->nodeFactory,
            [],
            ['paragraph']
        );

        $expected = '';

        $result = $markdownRenderer->render($doc, $this->context);
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

        $markdownRenderer = new MarkdownRenderer(
            $this->markFactory,
            $this->nodeFactory,
            [],
            ['paragraph', 'text']
        );

        $this->createNodeFactoryExpectation(['paragraph', $this->context], new Paragraph($this->context));
        $this->createNodeFactoryExpectation(['text', $this->context], new Text($this->context));
        $this->createContextGetMarkdownEscaperExpectation();
        $this->createMarkdownEscaperExpectation(['Hi!!!'], 'Hi I’m escaped!!!');

        $expected = "Hi I’m escaped!!!\n";

        $result = $markdownRenderer->render($doc, $this->context);
        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function textNodeTextIsNotEscapedWhenInsideACodeBlock()
    {
        $doc = [
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'code_block',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'Hi!!!',
                        ],
                    ],
                ],
            ],
        ];

        $markdownRenderer = new MarkdownRenderer(
            $this->markFactory,
            $this->nodeFactory,
            [],
            ['code_block', 'text']
        );

        $this->createNodeFactoryExpectation(['code_block', $this->context], new CodeBlock($this->context));
        $this->createNodeFactoryExpectation(['text', $this->context], new Text($this->context));

        $expected = "```\nHi!!!\n```\n";

        $result = $markdownRenderer->render($doc, $this->context);
        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function textNodeTextIsNotEscapedWhenMarkedWithCode()
    {
        $doc = [
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'paragraph',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'Ansible allows you to assign groups to other groups using the ',
                        ],
                        [
                            'type' => 'text',
                            'marks' => [
                                [
                                    'type' => 'code',
                                ]
                            ],
                            'text' => '[groupname:children]',
                        ],
                        [
                            'type' => 'text',
                            'text' => ' syntax in the inventory.',
                        ],
                    ],
                ],
            ],
        ];

        $markdownRenderer = new MarkdownRenderer(
            $this->markFactory,
            $this->nodeFactory,
            ['code'],
            ['paragraph', 'text']
        );

        $this->createNodeFactoryExpectation(['paragraph', $this->context], new Paragraph($this->context));
        $this->createNodeFactoryExpectation(['text', $this->context], new Text($this->context));
        $this->createContextGetMarkdownEscaperExpectation();
        $this->createMarkdownEscaperExpectation(
            ['Ansible allows you to assign groups to other groups using the '],
            'Ansible allows you to assign groups to other groups using the '
        );
        $this->createNodeFactoryExpectation(['text', $this->context], new Text($this->context));
        $this->createMarkFactoryExpectation(['code', $this->context], new Code($this->context));
        $this->createNodeFactoryExpectation(['text', $this->context], new Text($this->context));
        $this->createContextGetMarkdownEscaperExpectation();
        $this->createMarkdownEscaperExpectation(
            [' syntax in the inventory.'],
            ' syntax in the inventory.'
        );

        // phpcs:disable Generic.Files.LineLength
        $expected = "Ansible allows you to assign groups to other groups using the `[groupname:children]` syntax in the inventory.\n";
        // phpcs:enable

        $result = $markdownRenderer->render($doc, $this->context);
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

        $markdownRenderer = new MarkdownRenderer(
            $this->markFactory,
            $this->nodeFactory,
            [],
            ['paragraph', 'hard_break']
        );

        $this->createNodeFactoryExpectation(['paragraph', $this->context], new Paragraph($this->context));
        $this->createNodeFactoryExpectation(['hard_break', $this->context], new HardBreak($this->context));

        $expected = "\n";

        $result = $markdownRenderer->render($doc, $this->context);
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

        $markdownRenderer = new MarkdownRenderer(
            $this->markFactory,
            $this->nodeFactory,
            ['emphasis'],
            ['paragraph', 'text']
        );

        $this->createNodeFactoryExpectation(['paragraph', $this->context], new Paragraph($this->context));
        $this->createNodeFactoryExpectation(['text', $this->context], new Text($this->context));
        $this->createContextGetMarkdownEscaperExpectation();
        $this->createMarkdownEscaperExpectation(['We are '], 'We are ');
        $this->createNodeFactoryExpectation(['text', $this->context], new Text($this->context));
        $this->createContextGetMarkdownEscaperExpectation();
        $this->createMarkdownEscaperExpectation(['bold'], 'bold');
        $this->createNodeFactoryExpectation(['text', $this->context], new Text($this->context));
        $this->createContextGetMarkdownEscaperExpectation();
        $this->createMarkdownEscaperExpectation([' and '], ' and ');
        $this->createNodeFactoryExpectation(['text', $this->context], new Text($this->context));
        $this->createContextGetMarkdownEscaperExpectation();
        $this->createMarkdownEscaperExpectation(['italic'], 'italic');
        $this->createMarkFactoryExpectation(['emphasis', $this->context], new Emphasis($this->context));
        $this->createNodeFactoryExpectation(['text', $this->context], new Text($this->context));
        $this->createContextGetMarkdownEscaperExpectation();
        $this->createMarkdownEscaperExpectation(['.'], '.');

        $expected = "We are bold and *italic*.\n";

        $result = $markdownRenderer->render($doc, $this->context);
        $this->assertEquals($expected, $result);
    }

    private function createMarkdownEscaperExpectation($args, $result)
    {
        $this->markdownEscaper
            ->shouldReceive('escapeInnerText')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createContextGetMarkdownEscaperExpectation()
    {
        $this->context
            ->shouldReceive('getMarkdownEscaper')
            ->once()
            ->andReturn($this->markdownEscaper);
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
