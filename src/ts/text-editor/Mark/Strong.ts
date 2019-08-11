import { MarkInterface } from 'text-editor/Mark/MarkInterface';

export class Strong implements MarkInterface {
    get name(): string {
        return 'strong';
    }

    get spec(): object {
        return {
            parseDOM: [
                {
                    tag: 'strong'
                },
                {
                    // This works around a Google Docs misbehavior where
                    // pasted content will be inexplicably wrapped in `<b>`
                    // tags with a font-weight normal.
                    tag: 'b',
                    getAttrs: (node: HTMLElement) => node.style.fontWeight !== 'normal' && null,
                },
                {
                    style: 'font-weight',
                    getAttrs: (value: string) => /^(bold(er)?|[5-9]\d{2,})$/.test(value) && null,
                }
            ],
            toDOM(): (string | number)[] {
                return ['strong', 0];
            }
        };
    }
}
