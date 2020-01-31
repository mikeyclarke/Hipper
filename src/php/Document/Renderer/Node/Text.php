<?php
declare(strict_types=1);

namespace Hipper\Document\Renderer\Node;

use Hipper\Document\Renderer\HtmlFragmentRendererContext;

class Text implements NodeInterface
{
    private HtmlFragmentRendererContext $context;

    public function __construct(
        HtmlFragmentRendererContext $context
    ) {
        $this->context = $context;
    }

    public function isText(): bool
    {
        return true;
    }

    public function isLeaf(): bool
    {
        return false;
    }

    public function getHtmlTags(?array $attributes, ?string $htmlId): ?array
    {
        return null;
    }

    public function toPlainTextString(string $textContent): string
    {
        return null;
    }

    public function toMarkdownString(
        string $content,
        int $index,
        ?NodeInterface $parentNode,
        ?array $attributes
    ): string {
        return '';
    }
}
