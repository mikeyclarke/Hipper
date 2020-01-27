import NodeInterface from 'text-editor/Node/NodeInterface';

export default class HorizontalRule implements NodeInterface {
    get name(): string {
        return 'horizontal_rule';
    }

    get spec(): object {
        return {
            group: 'block',
            parseDOM: [
                {
                    tag: 'hr',
                },
            ],
            toDOM(): string[] {
                return ['hr'];
            },
        };
    }
}
