import { Node as ProseMirrorNode } from 'prosemirror-model';
import NodeInterface from 'text-editor/Node/NodeInterface';

export default class Heading implements NodeInterface {
    get name(): string {
        return 'heading';
    }

    get spec(): object {
        return {
            attrs: {
                level: {
                    default: 1,
                },
            },
            content: 'inline*',
            group: 'block',
            defining: true,
            parseDOM: [
                {
                    tag: 'h1',
                    attrs: {
                        level: 1,
                    },
                },
                {
                    tag: 'h2',
                    attrs: {
                        level: 2,
                    },
                },
                {
                    tag: 'h3',
                    attrs: {
                        level: 3,
                    },
                },
                {
                    tag: 'h4',
                    attrs: {
                        level: 4,
                    },
                },
                {
                    tag: 'h5',
                    attrs: {
                        level: 5,
                    },
                },
                {
                    tag: 'h6',
                    attrs: {
                        level: 6,
                    },
                },
            ],
            toDOM(node: ProseMirrorNode): (string | number)[] {
                return ['h' + node.attrs.level, 0];
            },
        };
    }
}
