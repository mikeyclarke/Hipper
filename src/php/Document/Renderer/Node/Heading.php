<?php
declare(strict_types=1);

namespace Hipper\Document\Renderer\Node;

use Hipper\Document\Renderer\HtmlFragmentRendererContext;

class Heading implements NodeInterface
{
    const DEFAULT_LEVEL = 1;

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
        $level = $this->getLevel($attributes);

        $tagName = sprintf('h%d', $level);
        $openingTag = "<{$tagName}>";
        if (null !== $htmlId) {
            $openingTag = "<{$tagName} id=\"{$htmlId}\">";
        }

        return [$openingTag, "</{$tagName}>"];
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
        $level = $this->getLevel($attributes);
        $prefix = str_repeat('#', $level);

        $result = "{$prefix} {$content}\n\n";

        return $result;
    }

    private function getLevel(?array $attributes): int
    {
        $level = self::DEFAULT_LEVEL;
        if (null !== $attributes && isset($attributes['level'])) {
            $attrLevel = (int) $attributes['level'];
            if ($attrLevel >= 1 && $attrLevel <= 6) {
                $level = $attrLevel;
            }
        }

        return $level;
    }
}
