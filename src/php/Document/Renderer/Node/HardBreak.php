<?php
declare(strict_types=1);

namespace Hipper\Document\Renderer\Node;

use Hipper\Document\Renderer\HtmlFragmentRendererContext;

class HardBreak implements NodeInterface
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
        return ['<br>'];
    }

    public function toPlainTextString(string $textContent): string
    {
        return '';
    }
}
