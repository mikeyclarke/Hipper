import { NodeInterface } from 'text-editor/Node/NodeInterface';

export class ListItem implements NodeInterface {
    get name(): string {
        return 'list_item';
    }

    get spec(): object {
        return {
            content: 'paragraph block*',
            defining: true,
            parseDOM: [
                {
                    tag: 'li'
                }
            ],
            toDOM(): (string | number)[] {
                return ['li', 0];
            }
        };
    }
}
