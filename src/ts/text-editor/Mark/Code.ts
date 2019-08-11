import { MarkInterface } from 'text-editor/Mark/MarkInterface';

export class Code implements MarkInterface {
    get name(): string {
        return 'code';
    }

    get spec(): object {
        return {
            excludes: '_',
            parseDOM: [
                {
                    tag: 'code'
                }
            ],
            toDOM(): (string | object | number)[] {
                return ['code', { spellcheck: 'false' }, 0];
            }
        };
    }
}
