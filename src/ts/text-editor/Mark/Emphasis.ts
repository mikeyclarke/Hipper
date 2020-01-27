import MarkInterface from 'text-editor/Mark/MarkInterface';

export default class Emphasis implements MarkInterface {
    get name(): string {
        return 'emphasis';
    }

    get spec(): object {
        return {
            parseDOM: [
                {
                    tag: 'i',
                },
                {
                    tag: 'em',
                },
                {
                    style: 'font-style=italic',
                },
            ],
            toDOM(): (string | number)[] {
                return ['em', 0];
            },
        };
    }
}
