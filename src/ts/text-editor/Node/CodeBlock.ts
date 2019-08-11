import { NodeInterface } from 'text-editor/Node/NodeInterface';

export class CodeBlock implements NodeInterface {
    get name(): string {
        return 'code_block';
    }

    get spec(): object {
        return {
            content: 'text*',
            marks: '',
            group: 'block',
            code: true,
            defining: true,
            parseDOM: [
                {
                    tag: 'pre',
                    preserveWhitespace: 'full'
                }
            ],
            toDOM(): (string | object)[] {
                return ['pre', { spellcheck: 'false' }, ['code', 0]];
            }
        };
    }
}
