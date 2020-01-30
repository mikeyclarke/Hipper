<?php
declare(strict_types=1);

namespace Hipper\Document\Renderer;

use Hipper\Document\Renderer\Node\NodeFactory;
use Hipper\Document\Renderer\RendererInterface;

class PlainTextRenderer implements RendererInterface
{
    private NodeFactory $nodeFactory;
    private array $allowedNodes;

    public function __construct(
        NodeFactory $nodeFactory,
        array $allowedNodes
    ) {
        $this->nodeFactory = $nodeFactory;
        $this->allowedNodes = $allowedNodes;
    }

    public function render(array $doc, HtmlFragmentRendererContext $context): string
    {
        $result = '';
        foreach ($doc['content'] as $node) {
            $result .= $this->renderNode($node, $context);
        }

        return $result;
    }

    private function renderNode($node, HtmlFragmentRendererContext $context): string
    {
        if (!is_array($node) || !isset($node['type'])) {
            return '';
        }

        if (!in_array($node['type'], $this->allowedNodes)) {
            return '';
        }

        $class = $this->nodeFactory->create($node['type'], $context);

        if ($class->isText()) {
            return $this->renderText($node, $context);
        }

        $text = '';

        if (isset($node['content'])) {
            foreach ($node['content'] as $childNode) {
                $text .= self::renderNode($childNode, $context);
            }
        }

        if (empty(trim($text))) {
            return '';
        }

        if ($class->isLeaf()) {
            return $text;
        }

        return $class->toPlainTextString($text);
    }

    private function renderText(array $node, HtmlFragmentRendererContext $context): string
    {
        if (!isset($node['text'])) {
            return '';
        }

        $htmlEscaper = $context->getHtmlEscaper();
        $result = $htmlEscaper->escapeInnerText($node['text']);

        return $result;
    }

    private function isEmptyParagraphNode(array $node): bool
    {
        return $node['type'] === 'paragraph' && (!isset($node['content']) || empty($node['content']));
    }
}
