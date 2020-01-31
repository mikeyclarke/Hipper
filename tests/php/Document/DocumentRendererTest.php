<?php
declare(strict_types=1);

namespace Hipper\Document;

use Hipper\Document\DocumentRenderer;
use Hipper\Document\Renderer\Decoder;
use Hipper\Document\Renderer\DocumentOutlineGenerator;
use Hipper\Document\Renderer\DocumentOutlineHtmlIdsInjector;
use Hipper\Document\Renderer\Exception\UnsupportedRenderingFormatException;
use Hipper\Document\Renderer\HtmlFragmentRendererContext;
use Hipper\Document\Renderer\HtmlFragmentRendererContextFactory;
use Hipper\Document\Renderer\HtmlRenderer;
use Hipper\Document\Renderer\MarkdownRenderer;
use Hipper\Document\Renderer\PlainTextRenderer;
use Hipper\Document\Renderer\RendererResult;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class DocumentRendererTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private $contextFactory;
    private $decoder;
    private $documentOutlineGenerator;
    private $documentOutlineHtmlIdsInjector;
    private $htmlRenderer;
    private $markdownRenderer;
    private $plainTextRenderer;
    private $documentRenderer;
    private $organizationDomain;
    private $context;

    public function setUp(): void
    {
        $this->contextFactory = m::mock(HtmlFragmentRendererContextFactory::class);
        $this->decoder = m::mock(Decoder::class);
        $this->documentOutlineGenerator = m::mock(DocumentOutlineGenerator::class);
        $this->documentOutlineHtmlIdsInjector = m::mock(DocumentOutlineHtmlIdsInjector::class);
        $this->htmlRenderer = m::mock(HtmlRenderer::class);
        $this->markdownRenderer = m::mock(MarkdownRenderer::class);
        $this->plainTextRenderer = m::mock(PlainTextRenderer::class);

        $this->documentRenderer = new DocumentRenderer(
            $this->contextFactory,
            $this->decoder,
            $this->documentOutlineGenerator,
            $this->documentOutlineHtmlIdsInjector,
            $this->htmlRenderer,
            $this->markdownRenderer,
            $this->plainTextRenderer
        );

        $this->organizationDomain = 'acme.usehipper.com';
        $this->context = m::mock(HtmlFragmentRendererContext::class);
    }

    /**
     * @test
     */
    public function renderAsHtml()
    {
        $doc = '{"type": "doc", "content": [{"type": "heading", "content": [{"type": "text", "text": "Hello"}]}]}';

        $decoded = [
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'heading',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'Hello',
                        ],
                    ],
                ],
            ],
        ];
        $renderedContent = '<h1>Hello</h1>';

        $this->createDecoderExpectation([$doc], $decoded);
        $this->createContextFactoryExpectation();
        $this->createHtmlRendererExpectation([$decoded, $this->context], $renderedContent);

        $result = $this->documentRenderer->render($doc, 'html', $this->organizationDomain);
        $this->assertInstanceOf(RendererResult::class, $result);
        $this->assertEquals($renderedContent, $result->getContent());
    }

    /**
     * @test
     */
    public function renderAsHtmlWithOutline()
    {
        $doc = '{"type": "doc", "content": [{"type": "heading", "content": [{"type": "text", "text": "Hello"}]}]}';

        $decoded = [
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'heading',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'Hello',
                        ],
                    ],
                ],
            ],
        ];
        $decodedWithOutlineIds = [
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'heading',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'Hello',
                        ],
                    ],
                    'html_id' => '_hello',
                ],
            ],
        ];
        $outline = [
            [
                'id' => '_hello',
                'level' => 1,
                'text' => 'Hello',
            ],
        ];
        $renderedContent = '<h1>Hello</h1>';

        $this->createDecoderExpectation([$doc], $decoded);
        $this->createDocumentOutlineHtmlIdsInjectorExpectation([$decoded], $decodedWithOutlineIds);
        $this->createDocumentOutlineGeneratorExpectation([$decodedWithOutlineIds], $outline);
        $this->createContextFactoryExpectation();
        $this->createHtmlRendererExpectation([$decodedWithOutlineIds, $this->context], $renderedContent);

        $result = $this->documentRenderer->render($doc, 'html', $this->organizationDomain, true);
        $this->assertInstanceOf(RendererResult::class, $result);
        $this->assertEquals($renderedContent, $result->getContent());
        $this->assertEquals($outline, $result->getOutline());
    }

    /**
     * @test
     */
    public function renderAsPlainText()
    {
        $doc = '{"type": "doc", "content": [{"type": "heading", "content": [{"type": "text", "text": "Hello"}]}]}';

        $decoded = [
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'heading',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'Hello',
                        ],
                    ],
                ],
            ],
        ];
        $renderedContent = 'Hello';

        $this->createDecoderExpectation([$doc], $decoded);
        $this->createContextFactoryExpectation();
        $this->createPlainTextRendererExpectation([$decoded, $this->context], $renderedContent);

        $result = $this->documentRenderer->render($doc, 'text', $this->organizationDomain);
        $this->assertInstanceOf(RendererResult::class, $result);
        $this->assertEquals($renderedContent, $result->getContent());
    }

    /**
     * @test
     */
    public function renderAsMarkdown()
    {
        $doc = '{"type": "doc", "content": [{"type": "heading", "content": [{"type": "text", "text": "Hello"}]}]}';

        $decoded = [
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'heading',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'Hello',
                        ],
                    ],
                ],
            ],
        ];
        $renderedContent = '# Hello';

        $this->createDecoderExpectation([$doc], $decoded);
        $this->createContextFactoryExpectation();
        $this->createMarkdownRendererExpectation([$decoded, $this->context], $renderedContent);

        $result = $this->documentRenderer->render($doc, 'markdown', $this->organizationDomain);
        $this->assertInstanceOf(RendererResult::class, $result);
        $this->assertEquals($renderedContent, $result->getContent());
    }

    /**
     * @test
     */
    public function unsupportedFormat()
    {
        $doc = '{"type": "doc", "content": [{"type": "heading", "content": [{"type": "text", "text": "Hello"}]}]}';

        $this->expectException(UnsupportedRenderingFormatException::class);

        $this->documentRenderer->render($doc, 'poop', $this->organizationDomain);
    }

    private function createMarkdownRendererExpectation($args, $result)
    {
        $this->markdownRenderer
            ->shouldReceive('render')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createPlainTextRendererExpectation($args, $result)
    {
        $this->plainTextRenderer
            ->shouldReceive('render')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createHtmlRendererExpectation($args, $result)
    {
        $this->htmlRenderer
            ->shouldReceive('render')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createContextFactoryExpectation()
    {
        $this->contextFactory
            ->shouldReceive('create')
            ->once()
            ->with($this->organizationDomain)
            ->andReturn($this->context);
    }

    private function createDocumentOutlineGeneratorExpectation($args, $result)
    {
        $this->documentOutlineGenerator
            ->shouldReceive('generate')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createDocumentOutlineHtmlIdsInjectorExpectation($args, $result)
    {
        $this->documentOutlineHtmlIdsInjector
            ->shouldReceive('inject')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createDecoderExpectation($args, $result)
    {
        $this->decoder
            ->shouldReceive('decode')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }
}
