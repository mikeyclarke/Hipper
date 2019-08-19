<?php
declare(strict_types=1);

namespace Hipper\Document\Renderer\Node;

use Hipper\Document\Renderer\HtmlFragmentRendererContext;

class Heading implements NodeInterface
{
    const DEFAULT_LEVEL = 1;

    private $context;

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

    public function getHtmlTags(?array $attributes): ?array
    {
        $level = self::DEFAULT_LEVEL;
        if (null !== $attributes && isset($attributes['level'])) {
            $attrLevel = (int) $attributes['level'];
            if ($attrLevel >= 1 && $attrLevel <= 6) {
                $level = $attrLevel;
            }
        }

        $tagName = sprintf('h%d', $level);

        return ["<{$tagName}>", "</{$tagName}>"];
    }
}
