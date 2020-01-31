<?php
declare(strict_types=1);

namespace Hipper\Document\Renderer\Mark;

interface MarkInterface
{
    public function getHtmlTags(?array $attributes): ?array;

    public function toMarkdownString(string $text, ?array $attributes): string;
}
