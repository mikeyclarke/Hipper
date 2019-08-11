import { MarkInterface } from 'text-editor/Mark/MarkInterface';

export class Strike implements MarkInterface {
    get name(): string {
        return 'strike';
    }

    get spec(): object {
        return {
            parseDOM: [
                {
                    tag: 's'
                },
                {
                    tag: 'strike'
                },
                {
                    tag: 'del'
                }
            ],
            toDOM(): (string | number)[] {
                return ['s', 0];
            }
        };
    }
}
