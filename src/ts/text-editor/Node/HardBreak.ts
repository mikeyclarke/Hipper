import NodeInterface from 'text-editor/Node/NodeInterface';

export default class HardBreak implements NodeInterface {
    get name(): string {
        return 'hard_break';
    }

    get spec(): object {
        return {
            inline: true,
            group: 'inline',
            selectable: false,
            parseDOM: [
                {
                    tag: 'br',
                },
            ],
            toDOM(): string[] {
                return ['br'];
            },
        };
    }
}
