<?php
declare(strict_types=1);

namespace Hipper\Document\Renderer\Node;

use Hipper\Document\Renderer\Exception\InvalidNodeException;
use Hipper\Document\Renderer\HtmlFragmentRendererContext;
use Hipper\Document\Renderer\Node\NodeInterface;

class NodeFactory
{
    const NODE_CLASS_MAP = [
        'blockquote' => Blockquote::class,
        'code_block' => CodeBlock::class,
        'hard_break' => HardBreak::class,
        'heading' => Heading::class,
        'horizontal_rule' => HorizontalRule::class,
        'image' => Image::class,
        'list_item' => ListItem::class,
        'ordered_list' => OrderedList::class,
        'paragraph' => Paragraph::class,
        'text' => Text::class,
        'unordered_list' => UnorderedList::class,
    ];

    public function create(string $name, HtmlFragmentRendererContext $context)
    {
        if (!isset(self::NODE_CLASS_MAP[$name])) {
            throw new InvalidNodeException;
        }

        $class = self::NODE_CLASS_MAP[$name];
        return new $class($context);
    }
}
