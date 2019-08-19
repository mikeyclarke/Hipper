<?php
declare(strict_types=1);

namespace Hipper\Document\Renderer\Node;

use Hipper\Document\Renderer\HtmlFragmentRendererContext;

class Image implements NodeInterface
{
    private $context;

    public function __construct(
        HtmlFragmentRendererContext $context
    ) {
        $this->context = $context;
    }

    public function isText(): bool
    {
        return false;
    }

    public function isLeaf(): bool
    {
        return true;
    }

    public function getHtmlTags(?array $attributes): ?array
    {
        if (null === $attributes || !isset($attributes['src']) || null === $attributes['src']) {
            return null;
        }

        $urlAttributeValidator = $this->context->getUrlAttributeValidator();
        if (!$urlAttributeValidator->isValid($attributes['src'])) {
            return null;
        }

        $htmlEscaper = $this->context->getHtmlEscaper();
        $supportedAttributes = ['alt', 'src', 'title'];
        $htmlAttributes = [];

        foreach ($supportedAttributes as $attribute) {
            if (isset($attributes[$attribute]) && null !== $attributes[$attribute]) {
                $htmlAttributes[] = sprintf(
                    '%s="%s"',
                    $attribute,
                    $htmlEscaper->escapeAttributeValue($attributes[$attribute])
                );
            }
        }

        $tag = sprintf('<img %s>', implode(' ', $htmlAttributes));
        return [$tag];
    }
}
