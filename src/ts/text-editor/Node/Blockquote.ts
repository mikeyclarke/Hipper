import { NodeInterface } from 'text-editor/Node/NodeInterface.ts';

export class Blockquote implements NodeInterface {
    get name(): string {
        return 'blockquote';
    }

    get spec(): object {
        return {
            content: 'block+',
            group: 'block',
            defining: true,
            parseDOM: [
                {
                    tag: 'blockquote'
                }
            ],
            toDOM(): (string | number)[] {
                return ['blockquote', 0];
            }
        };
    }
}
