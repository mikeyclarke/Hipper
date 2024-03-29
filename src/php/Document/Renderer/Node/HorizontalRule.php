<?php
declare(strict_types=1);

namespace Hipper\Document\Renderer\Node;

use Hipper\Document\Renderer\HtmlFragmentRendererContext;

class HorizontalRule implements NodeInterface
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
        return ['<hr>'];
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
        return "- - - - - -\n\n";
    }
}
