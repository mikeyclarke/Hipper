<?php
declare(strict_types=1);

namespace Hipper\Document\Renderer;

use Hipper\Document\Renderer\HtmlEscaper;
use Hipper\Document\Renderer\UrlAttributeValidator;

class HtmlFragmentRendererContext
{
    private $htmlEscaper;
    private $urlAttributeValidator;
    private $organizationDomain;

    public function __construct(
        HtmlEscaper $htmlEscaper,
        UrlAttributeValidator $urlAttributeValidator,
        string $organizationDomain
    ) {
        $this->htmlEscaper = $htmlEscaper;
        $this->urlAttributeValidator = $urlAttributeValidator;
        $this->organizationDomain = $organizationDomain;
    }

    public function getHtmlEscaper(): HtmlEscaper
    {
        return $this->htmlEscaper;
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
