<?php
declare(strict_types=1);

namespace Hipper\Document\Renderer;

class HtmlFragmentRendererContextFactory
{
    private HtmlEscaper $htmlEscaper;
    private MarkdownEscaper $markdownEscaper;
    private StringTerminator $stringTerminator;
    private UrlAttributeValidator $urlAttributeValidator;

    public function __construct(
        HtmlEscaper $htmlEscaper,
        MarkdownEscaper $markdownEscaper,
        StringTerminator $stringTerminator,
        UrlAttributeValidator $urlAttributeValidator
    ) {
        $this->htmlEscaper = $htmlEscaper;
        $this->markdownEscaper = $markdownEscaper;
        $this->stringTerminator = $stringTerminator;
        $this->urlAttributeValidator = $urlAttributeValidator;
    }

    public function create(string $organizationDomain): HtmlFragmentRendererContext
    {
        return new HtmlFragmentRendererContext(
            $this->htmlEscaper,
            $this->markdownEscaper,
            $this->stringTerminator,
            $this->urlAttributeValidator,
            $organizationDomain
        );
    }
}
