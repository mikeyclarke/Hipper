<?php
declare(strict_types=1);

namespace Hipper\Document\Renderer;

use Hipper\Document\Renderer\HtmlFragmentRendererContextFactory;

interface RendererInterface
{
    public function render(array $doc, HtmlFragmentRendererContext $context): string;
}
