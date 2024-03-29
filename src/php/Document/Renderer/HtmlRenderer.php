<?php
declare(strict_types=1);

namespace Hipper\Document\Renderer;

use Hipper\Document\Renderer\Exception\InvalidLeafNodeHtmlTagsException;
use Hipper\Document\Renderer\Exception\InvalidNodeHtmlTagsException;
use Hipper\Document\Renderer\HtmlFragmentRendererContextFactory;
use Hipper\Document\Renderer\Mark\MarkFactory;
use Hipper\Document\Renderer\Node\NodeFactory;
use Hipper\Document\Renderer\RendererInterface;

class HtmlRenderer implements RendererInterface
{
    private $markFactory;
    private $nodeFactory;
    private $allowedMarks;
    private $allowedNodes;

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

        $htmlId = $node['html_id'] ?? null;
        $nodeAttrs = $node['attrs'] ?? null;

        $tags = $class->getHtmlTags($nodeAttrs, $htmlId);
        if (null === $tags) {
            return '';
        }

        if ($class->isLeaf()) {
            if (count($tags) !== 1) {
                throw new InvalidLeafNodeHtmlTagsException;
            }
            return $tags[0];
        }

        if ($this->isEmptyParagraphNode($node)) {
            $node['content'] = [['type' => 'hard_break']];
        }

        $innerHtml = '';
        if (isset($node['content'])) {
            foreach ($node['content'] as $childNode) {
                $innerHtml .= self::renderNode($childNode, $context);
            }
        }

        if (count($tags) !== 2) {
            throw new InvalidNodeHtmlTagsException;
        }

        return $tags[0] . $innerHtml . $tags[1];
    }

    private function renderMark(array $mark, string $content, HtmlFragmentRendererContext $context): string
    {
        if (!in_array($mark['type'], $this->allowedMarks)) {
            return $content;
        }

        $class = $this->markFactory->create($mark['type'], $context);

        $markAttrs = $mark['attrs'] ?? null;
        $tags = $class->getHtmlTags($markAttrs);
        if (null === $tags) {
            return $content;
        }

        return $tags[0] . $content . $tags[1];
    }

    private function renderText(array $node, HtmlFragmentRendererContext $context): string
    {
        if (!isset($node['text'])) {
            return '';
        }

        $htmlEscaper = $context->getHtmlEscaper();
        $result = $htmlEscaper->escapeInnerText($node['text']);

        if (isset($node['marks'])) {
            foreach ($node['marks'] as $mark) {
                $result = $this->renderMark($mark, $result, $context);
            }
        }

        return $result;
    }

    private function isEmptyParagraphNode(array $node): bool
    {
        return $node['type'] === 'paragraph' && (!isset($node['content']) || empty($node['content']));
    }
}
