<?php
declare(strict_types=1);

namespace Hipper\Document\Renderer;

class HtmlFragmentRendererContextFactory
{
    private HtmlEscaper $htmlEscaper;
    private StringTerminator $stringTerminator;
    private UrlAttributeValidator $urlAttributeValidator;

    public function __construct(
        HtmlEscaper $htmlEscaper,
        StringTerminator $stringTerminator,
        UrlAttributeValidator $urlAttributeValidator
    ) {
        $this->htmlEscaper = $htmlEscaper;
        $this->stringTerminator = $stringTerminator;
        $this->urlAttributeValidator = $urlAttributeValidator;
    }

    public function create(string $organizationDomain): HtmlFragmentRendererContext
    {
        return new HtmlFragmentRendererContext(
            $this->htmlEscaper,
            $this->stringTerminator,
            $this->urlAttributeValidator,
            $organizationDomain
        );
    }
}
