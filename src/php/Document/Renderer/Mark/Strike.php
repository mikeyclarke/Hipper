<?php
declare(strict_types=1);

namespace Hipper\Document\Renderer\Mark;

use Hipper\Document\Renderer\HtmlFragmentRendererContext;

class Strike implements MarkInterface
{
    private $context;

    public function __construct(
        HtmlFragmentRendererContext $context
    ) {
        $this->context = $context;
    }

    public function getHtmlTags(?array $attributes): ?array
    {
        return ['<s>', '</s>'];
    }

    public function toMarkdownString(string $text, ?array $attributes): string
    {
        return "~~{$text}~~";
    }
}
