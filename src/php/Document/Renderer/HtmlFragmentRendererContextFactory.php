<?php
declare(strict_types=1);

namespace Hipper\Document\Renderer;

class HtmlFragmentRendererContextFactory
{
    private $htmlEscaper;
    private $urlAttributeValidator;

    public function __construct(
        HtmlEscaper $htmlEscaper,
        UrlAttributeValidator $urlAttributeValidator
    ) {
        $this->htmlEscaper = $htmlEscaper;
        $this->urlAttributeValidator = $urlAttributeValidator;
    }

    public function create(string $organizationDomain): HtmlFragmentRendererContext
    {
        return new HtmlFragmentRendererContext(
            $this->htmlEscaper,
            $this->urlAttributeValidator,
            $organizationDomain
        );
    }
}
