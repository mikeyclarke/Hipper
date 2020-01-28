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
}
