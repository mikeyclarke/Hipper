<?php
declare(strict_types=1);

namespace Hipper\Document;

use Hipper\Document\Renderer\Decoder;
use Hipper\Document\Renderer\DocumentOutlineGenerator;
use Hipper\Document\Renderer\DocumentOutlineHtmlIdsInjector;
use Hipper\Document\Renderer\Exception\ContentDecodeException;
use Hipper\Document\Renderer\Exception\UnsupportedRenderingFormatException;
use Hipper\Document\Renderer\HtmlFragmentRendererContextFactory;
use Hipper\Document\Renderer\HtmlRenderer;
use Hipper\Document\Renderer\MarkdownRenderer;
use Hipper\Document\Renderer\PlainTextRenderer;
use Hipper\Document\Renderer\RendererResult;

class DocumentRenderer
{
    private HtmlFragmentRendererContextFactory $contextFactory;
    private Decoder $decoder;
    private DocumentOutlineGenerator $documentOutlineGenerator;
    private DocumentOutlineHtmlIdsInjector $documentOutlineHtmlIdsInjector;
    private HtmlRenderer $htmlRenderer;
    private MarkdownRenderer $markdownRenderer;
    private PlainTextRenderer $plainTextRenderer;

    public function __construct(
        HtmlFragmentRendererContextFactory $contextFactory,
        Decoder $decoder,
        DocumentOutlineGenerator $documentOutlineGenerator,
        DocumentOutlineHtmlIdsInjector $documentOutlineHtmlIdsInjector,
        HtmlRenderer $htmlRenderer,
        MarkdownRenderer $markdownRenderer,
        PlainTextRenderer $plainTextRenderer
    ) {
        $this->contextFactory = $contextFactory;
        $this->decoder = $decoder;
        $this->documentOutlineGenerator = $documentOutlineGenerator;
        $this->documentOutlineHtmlIdsInjector = $documentOutlineHtmlIdsInjector;
        $this->htmlRenderer = $htmlRenderer;
        $this->markdownRenderer = $markdownRenderer;
        $this->plainTextRenderer = $plainTextRenderer;
    }

    public function render(
        $doc,
        string $format,
        string $organizationDomain = '',
        bool $generateOutline = false
    ): RendererResult {
        $result = new RendererResult;

        switch ($format) {
            case 'html':
                $renderer = $this->htmlRenderer;
                break;
            case 'text':
                $renderer = $this->plainTextRenderer;
                break;
            case 'markdown':
                $renderer = $this->markdownRenderer;
                break;
            default:
                throw new UnsupportedRenderingFormatException;
        }

        try {
            $decoded = $this->decodeDoc($doc);
        } catch (ContentDecodeException $e) {
            return $result;
        }

        if ($generateOutline) {
            $decoded = $this->documentOutlineHtmlIdsInjector->inject($decoded);
            $outline = $this->documentOutlineGenerator->generate($decoded);
            $result->setOutline($outline);
        }

        $context = $this->contextFactory->create($organizationDomain);

        $rendered = $renderer->render($decoded, $context);
        $result->setContent($rendered);

        return $result;
    }

    private function decodeDoc($doc): array
    {
        if (is_array($doc)) {
            return $doc;
        }
        return $this->decoder->decode($doc);
    }
}
