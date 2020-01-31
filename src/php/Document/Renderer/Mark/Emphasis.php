<?php
declare(strict_types=1);

namespace Hipper\Document\Renderer\Mark;

use Hipper\Document\Renderer\HtmlFragmentRendererContext;

class Emphasis implements MarkInterface
{
    private $context;

    public function __construct(
        HtmlFragmentRendererContext $context
    ) {
        $this->context = $context;
    }

    public function getHtmlTags(?array $attributes): ?array
    {
        return ['<em>', '</em>'];
    }

    public function toMarkdownString(string $text, ?array $attributes): string
    {
        return "*{$text}*";
    }
}
