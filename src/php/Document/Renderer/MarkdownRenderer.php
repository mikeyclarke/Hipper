<?php
declare(strict_types=1);

namespace Hipper\Document\Renderer;

use Hipper\Document\Renderer\Mark\MarkFactory;
use Hipper\Document\Renderer\Node\CodeBlock;
use Hipper\Document\Renderer\Node\NodeFactory;
use Hipper\Document\Renderer\Node\NodeInterface;

class MarkdownRenderer implements RendererInterface
{
    private MarkFactory $markFactory;
    private NodeFactory $nodeFactory;
    private array $allowedMarks;
    private array $allowedNodes;

    public function __construct(
        MarkFactory $markFactory,
        NodeFactory $nodeFactory,
        array $allowedMarks,
        array $allowedNodes
    ) {
        $this->markFactory = $markFactory;
        $this->nodeFactory = $nodeFactory;
        $this->allowedMarks = $allowedMarks;
        $this->allowedNodes = $allowedNodes;
    }

    public function render(array $doc, HtmlFragmentRendererContext $context): string
    {
        $result = '';
        foreach ($doc['content'] as $node) {
            $result .= $this->renderNode($node, 0, $context, null);
        }

        return $result;
    }

    private function renderNode(
        $node,
        int $index,
        HtmlFragmentRendererContext $context,
        ?NodeInterface $parentNode
    ): string {
        if (!is_array($node) || !isset($node['type'])) {
            return '';
        }

        if (!in_array($node['type'], $this->allowedNodes)) {
            return '';
        }

        $class = $this->nodeFactory->create($node['type'], $context);

        if ($class->isText()) {
            return $this->renderText($node, $context, $parentNode);
        }

        $nodeAttrs = $node['attrs'] ?? null;

        if ($this->isEmptyParagraphNode($node)) {
            $node['content'] = [['type' => 'hard_break']];
        }

        $innerContent = '';
        if (isset($node['content'])) {
            foreach ($node['content'] as $i => $childNode) {
                $innerContent .= self::renderNode($childNode, $i, $context, $class);
            }
        }

        return $class->toMarkdownString($innerContent, $index, $parentNode, $nodeAttrs);
    }

    private function renderMark(array $mark, string $content, HtmlFragmentRendererContext $context): string
    {
        if (!in_array($mark['type'], $this->allowedMarks)) {
            return $content;
        }

        $class = $this->markFactory->create($mark['type'], $context);

        $markAttrs = $mark['attrs'] ?? null;
        $result = $class->toMarkdownString($content, $markAttrs);

        return $result;
    }

    private function renderText(array $node, HtmlFragmentRendererContext $context, ?NodeInterface $parentNode): string
    {
        if (!isset($node['text'])) {
            return '';
        }

        $result = $node['text'];

        if ($parentNode instanceof CodeBlock) {
            return $result;
        }

        if (!$this->hasCodeMark($node)) {
            $markdownEscaper = $context->getMarkdownEscaper();
            $result = $markdownEscaper->escapeInnerText($result);
        }

        if (isset($node['marks'])) {
            foreach ($node['marks'] as $mark) {
                $result = $this->renderMark($mark, $result, $context);
            }
        }

        return $result;
    }

    private function hasCodeMark(array $node): bool
    {
        if (!isset($node['marks'])) {
            return false;
        }

        foreach ($node['marks'] as $mark) {
            if ($mark['type'] === 'code') {
                return true;
            }
        }

        return false;
    }

    private function isEmptyParagraphNode(array $node): bool
    {
        return $node['type'] === 'paragraph' && (!isset($node['content']) || empty($node['content']));
    }
}
