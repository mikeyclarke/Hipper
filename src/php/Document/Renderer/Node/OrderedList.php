<?php
declare(strict_types=1);

namespace Hipper\Document\Renderer\Node;

use Hipper\Document\Renderer\HtmlFragmentRendererContext;

class OrderedList implements NodeInterface
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
        return false;
    }

    public function getHtmlTags(?array $attributes, ?string $htmlId): ?array
    {
        $start = 0;
        if (null !== $attributes && isset($attributes['start'])) {
            $start = (int) $attributes['start'];
        }

        $openingTag = '<ol>';
        if ($start > 0) {
            $openingTag = sprintf('<ol start="%d">', $start);
        }

        return [$openingTag, '</ol>'];
    }

    public function toPlainTextString(string $textContent): string
    {
        return $textContent;
    }

    public function toMarkdownString(
        string $content,
        int $index,
        ?NodeInterface $parentNode,
        ?array $attributes
    ): string {
        $result = "{$content}\n";
        return $result;
    }
}
