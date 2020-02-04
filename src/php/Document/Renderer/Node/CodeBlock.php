<?php
declare(strict_types=1);

namespace Hipper\Document\Renderer\Node;

use Hipper\Document\Renderer\HtmlFragmentRendererContext;

class CodeBlock implements NodeInterface
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
        return ['<pre><code>', '</code></pre>'];
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

        $result = "```\n";
        foreach ($lines as $i => $line) {
            $result .= "{$line}";
            $isLastLine = (($i + 1) === $lineCount);
            if (!$isLastLine || !empty(trim($line))) {
                $result .= "\n";
            }
        }
        $result .= "```\n\n";

        return $result;
    }
}
