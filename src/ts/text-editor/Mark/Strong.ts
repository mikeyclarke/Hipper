import MarkInterface from 'text-editor/Mark/MarkInterface';

export default class Strong implements MarkInterface {
    get name(): string {
        return 'strong';
    }

    get spec(): object {
        return {
            parseDOM: [
                {
                    tag: 'strong',
                },
                {
                    // This works around a Google Docs misbehavior where
                    // pasted content will be inexplicably wrapped in `<b>`
                    // tags with a font-weight normal.
                    tag: 'b',
                    // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
                    getAttrs: (node: HTMLElement) => node.style.fontWeight !== 'normal' && null,
                },
                {
                    style: 'font-weight',
                    // eslint-disable-next-line @typescript-eslint/explicit-function-return-type
                    getAttrs: (value: string) => /^(bold(er)?|[5-9]\d{2,})$/.test(value) && null,
                },
            ],
            toDOM(): (string | number)[] {
                return ['strong', 0];
            },
        };
    }
}
