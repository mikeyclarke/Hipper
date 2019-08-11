import { NodeInterface } from 'text-editor/Node/NodeInterface';
import { Blockquote } from 'text-editor/Node/Blockquote';
import { CodeBlock } from 'text-editor/Node/CodeBlock';
import { Doc } from 'text-editor/Node/Doc';
import { HardBreak } from 'text-editor/Node/HardBreak';
import { Heading } from 'text-editor/Node/Heading';
import { HorizontalRule } from 'text-editor/Node/HorizontalRule';
import { Image } from 'text-editor/Node/Image';
import { ListItem } from 'text-editor/Node/ListItem';
import { OrderedList } from 'text-editor/Node/OrderedList';
import { Paragraph } from 'text-editor/Node/Paragraph';
import { Text } from 'text-editor/Node/Text';
import { UnorderedList } from 'text-editor/Node/UnorderedList';

export class NodeLoader {
    public getAllWithNames(names: string[]): NodeInterface[] {
        const available = [
            new Doc(),
            new Text(),
            new Paragraph(),
            new Heading(),
            new Blockquote(),
            new CodeBlock(),
            new HardBreak(),
            new HorizontalRule(),
            new Image(),
            new ListItem(),
            new OrderedList(),
            new UnorderedList(),
        ];

        return available.filter(node => names.includes(node.name));
    }
}
