<?php
declare(strict_types=1);

namespace Hipper\Document\Renderer\Node;

use Hipper\Document\Renderer\HtmlFragmentRendererContext;

class UnorderedList implements NodeInterface
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
        return ['<ul>', '</ul>'];
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

        if ($parentNode instanceof ListItem) {
            $lines = preg_split('/\r\n|\r|\n/', $content);
            $lineCount = count($lines);

            $result = "\n";
            foreach ($lines as $line) {
                $result .= "    {$line}\n";
            }
            $result .= "\n";
        }

        return $result;
    }
}
