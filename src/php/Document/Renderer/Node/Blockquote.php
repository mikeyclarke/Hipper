<?php
declare(strict_types=1);

namespace Hipper\Document\Renderer\Node;

use Hipper\Document\Renderer\HtmlFragmentRendererContext;

class Blockquote implements NodeInterface
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
        return ['<blockquote>', '</blockquote>'];
    }

    public function toPlainTextString(string $textContent): string
    {
        return $textContent . "\r\n";
    }

    public function toMarkdownString(
        string $content,
        int $index,
        ?NodeInterface $parentNode,
        ?array $attributes
    ): string {
        $lines = preg_split('/\r\n|\r|\n/', $content);
        $lineCount = count($lines);

        $result = '';
        foreach ($lines as $i => $line) {
            if (($i + 1) === $lineCount && empty(trim($line))) {
                continue;
            }
            $result .= "> {$line}\n";
        }
        $result .= "\n";

        return $result;
    }
}
