<?php
declare(strict_types=1);

namespace Hipper\Document\Renderer;

use Hipper\Document\Renderer\HtmlEscaper;
use Hipper\Document\Renderer\StringTerminator;
use Hipper\Document\Renderer\UrlAttributeValidator;

class HtmlFragmentRendererContext
{
    private HtmlEscaper $htmlEscaper;
    private MarkdownEscaper $markdownEscaper;
    private StringTerminator $stringTerminator;
    private UrlAttributeValidator $urlAttributeValidator;
    private string $organizationDomain;

    public function __construct(
        HtmlEscaper $htmlEscaper,
        MarkdownEscaper $markdownEscaper,
        StringTerminator $stringTerminator,
        UrlAttributeValidator $urlAttributeValidator,
        string $organizationDomain
    ) {
        $this->htmlEscaper = $htmlEscaper;
        $this->markdownEscaper = $markdownEscaper;
        $this->stringTerminator = $stringTerminator;
        $this->urlAttributeValidator = $urlAttributeValidator;
        $this->organizationDomain = $organizationDomain;
    }

    public function getHtmlEscaper(): HtmlEscaper
    {
        return $this->htmlEscaper;
    }

    public function getMarkdownEscaper(): MarkdownEscaper
    {
        return $this->markdownEscaper;
    }

    public function getStringTerminator(): StringTerminator
    {
        return $this->stringTerminator;
    }

    public function getUrlAttributeValidator(): UrlAttributeValidator
    {
        return $this->urlAttributeValidator;
    }

    public function getOrganizationDomain(): string
    {
        return $this->organizationDomain;
    }
}
