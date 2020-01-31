<?php
declare(strict_types=1);

namespace Hipper\Document\Renderer\Node;

use Hipper\Document\Renderer\HtmlFragmentRendererContext;

class Image implements NodeInterface
{
    private HtmlFragmentRendererContext $context;

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

    public function getHtmlTags(?array $attributes, ?string $htmlId): ?array
    {
        if (!$this->hasValidSrc($attributes)) {
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

    public function toPlainTextString(string $textContent): string
    {
        return '';
    }

    public function toMarkdownString(
        string $content,
        int $index,
        ?NodeInterface $parentNode,
        ?array $attributes
    ): string {
        if (!$this->hasValidSrc($attributes)) {
            return '';
        }

        $src = $attributes['src'];
        $alt = $attributes['alt'] ?? '';

        if (isset($attributes['title']) && !empty($attributes['title'])) {
            $title = $attributes['title'];
            return '![' . $alt . '](' . $src . ' "' . $title . '")' . "\n";
        }

        return "![{$alt}]({$src})\n";
    }

    private function hasValidSrc(?array $attributes): bool
    {
        if (null === $attributes || !isset($attributes['src'])) {
            return false;
        }

        $urlAttributeValidator = $this->context->getUrlAttributeValidator();
        if (!$urlAttributeValidator->isValid($attributes['src'])) {
            return false;
        }

        return true;
    }
}
