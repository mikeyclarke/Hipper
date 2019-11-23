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
use Hipper\Document\Renderer\RendererResult;

class DocumentRenderer
{
    private $contextFactory;
    private $decoder;
    private $documentOutlineGenerator;
    private $documentOutlineHtmlIdsInjector;
    private $htmlRenderer;

    public function __construct(
        HtmlFragmentRendererContextFactory $contextFactory,
        Decoder $decoder,
        DocumentOutlineGenerator $documentOutlineGenerator,
        DocumentOutlineHtmlIdsInjector $documentOutlineHtmlIdsInjector,
        HtmlRenderer $htmlRenderer
    ) {
        $this->contextFactory = $contextFactory;
        $this->decoder = $decoder;
        $this->documentOutlineGenerator = $documentOutlineGenerator;
        $this->documentOutlineHtmlIdsInjector = $documentOutlineHtmlIdsInjector;
        $this->htmlRenderer = $htmlRenderer;
    }

    public function render(
        $doc,
        string $format,
        string $organizationDomain,
        bool $generateOutline = false
    ): RendererResult {
        $result = new RendererResult;

        switch ($format) {
            case 'html':
                $renderer = $this->htmlRenderer;
                break;
            default:
                throw new UnsupportedRenderingFormatException;
        }

        try {
            $decoded = $this->decoder->decode($doc);
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
}
