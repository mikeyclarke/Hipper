<?php
declare(strict_types=1);

namespace Hipper\Document\Renderer\Mark;

use Hipper\Document\Renderer\HtmlFragmentRendererContext;

class Link implements MarkInterface
{
    private $context;

    public function __construct(
        HtmlFragmentRendererContext $context
    ) {
        $this->context = $context;
    }

    public function getHtmlTags(?array $attributes): ?array
    {
        if (!$this->hasValidHref($attributes)) {
            return null;
        }

        $htmlAttributes = [];
        $organizationDomain = $this->context->getOrganizationDomain();

        if ($organizationDomain !== parse_url($attributes['href'], PHP_URL_HOST)) {
            $htmlAttributes[] = 'rel="noopener noreferrer"';
        }

        $htmlEscaper = $this->context->getHtmlEscaper();
        $supportedAttributes = ['href', 'title'];

        foreach ($supportedAttributes as $attribute) {
            if (isset($attributes[$attribute]) && null !== $attributes[$attribute]) {
                $htmlAttributes[] = sprintf(
                    '%s="%s"',
                    $attribute,
                    $htmlEscaper->escapeAttributeValue($attributes[$attribute])
                );
            }
        }

        if (isset($attributes['spellcheck']) && is_bool($attributes['spellcheck']) && $attributes['spellcheck']) {
            $htmlAttributes[] = 'spellcheck="true"';
        }

        $openingTag = sprintf('<a %s>', implode(' ', $htmlAttributes));

        return [$openingTag, '</a>'];
    }

    public function toMarkdownString(string $text, ?array $attributes): string
    {
        if (!$this->hasValidHref($attributes)) {
            return $text;
        }

        $href = $attributes['href'];

        return "[{$text}]({$href})";
    }

    private function hasValidHref(?array $attributes): bool
    {
        if (null === $attributes || !isset($attributes['href'])) {
            return false;
        }

        $urlAttributeValidator = $this->context->getUrlAttributeValidator();
        if (!$urlAttributeValidator->isValid($attributes['href'])) {
            return false;
        }

        return true;
    }
}
