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
        if (null === $attributes || !isset($attributes['href']) || null === $attributes['href']) {
            return null;
        }

        $urlAttributeValidator = $this->context->getUrlAttributeValidator();
        if (!$urlAttributeValidator->isValid($attributes['href'])) {
            return null;
        }

        $htmlAttributes = [];
        $organizationDomain = $this->context->getOrganizationDomain();

        if (null === $organizationDomain || $organizationDomain !== parse_url($attributes['href'], PHP_URL_HOST)) {
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
}
