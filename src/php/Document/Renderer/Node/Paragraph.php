<?php
declare(strict_types=1);

namespace Hipper\Document\Renderer\Node;

use Hipper\Document\Renderer\HtmlFragmentRendererContext;

class Paragraph implements NodeInterface
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
        return ['<p>', '</p>'];
    }

    public function toPlainTextString(string $textContent): string
    {
        $stringTerminator = $this->context->getStringTerminator();
        $str = $stringTerminator->terminateStringWithPunctuationCharacter($textContent);
        return $str . "\r\n";
    }

    public function toMarkdownString(
        string $content,
        int $index,
        ?NodeInterface $parentNode,
        ?array $attributes
    ): string {
        $result = "{$content}\n";

        if (!$parentNode instanceof ListItem) {
            $result .= "\n";
        }

        return $result;
    }
}
