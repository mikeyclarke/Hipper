<?php
declare(strict_types=1);

namespace Hipper\Document\Renderer\Node;

interface NodeInterface
{
    public function isText(): bool;

    public function isLeaf(): bool;

    public function getHtmlTags(?array $attributes, ?string $htmlId): ?array;

    public function toPlainTextString(string $textContent): string;

    public function toMarkdownString(
        string $content,
        int $index,
        ?NodeInterface $parentNode,
        ?array $attributes
    ): string;
}
