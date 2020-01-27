import NodeInterface from 'text-editor/Node/NodeInterface';

export default class Paragraph implements NodeInterface {
    get name(): string {
        return 'paragraph';
    }

    get spec(): object {
        return {
            content: 'inline*',
            group: 'block',
            parseDOM: [
                {
                    tag: 'p',
                },
            ],
            toDOM(): (string | number)[] {
                return ['p', 0];
            },
        };
    }
}
