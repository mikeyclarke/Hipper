import NodeInterface from 'text-editor/Node/NodeInterface';

export default class UnorderedList implements NodeInterface {
    get name(): string {
        return 'unordered_list';
    }

    get spec(): object {
        return {
            content: 'list_item+',
            group: 'block',
            parseDOM: [
                {
                    tag: 'ul',
                },
            ],
            toDOM(): (string | number)[] {
                return ['ul', 0];
            },
        };
    }
}
