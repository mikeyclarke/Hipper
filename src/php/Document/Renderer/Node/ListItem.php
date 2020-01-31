<?php
declare(strict_types=1);

namespace Hipper\Document\Renderer\Node;

use Hipper\Document\Renderer\Exception\InvalidFragmentStructureException;
use Hipper\Document\Renderer\HtmlFragmentRendererContext;

class ListItem implements NodeInterface
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
        return ['<li>', '</li>'];
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
        $class = get_class($parentNode);
        switch ($class) {
            case OrderedList::class:
                $prefix = ($index + 1) . '.';
                break;
            case UnorderedList::class:
                $prefix = '-';
                break;
            default:
                throw new InvalidFragmentStructureException;
        }

        $result = "{$prefix} {$content}";

        return $result;
    }
}
