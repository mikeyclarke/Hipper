import { Node as ProsemirrorNode } from 'prosemirror-model';
import { MarkInterface } from 'text-editor/Mark/MarkInterface';

export class Link implements MarkInterface {
    get name(): string {
        return 'link';
    }

    get spec(): object {
        return {
            attrs: {
                href: {},
                title: {
                    default: null
                },
                spellcheck: {
                    default: null
                }
            },
            inclusive: false,
            parseDOM: [
                {
                    tag: 'a[href]:not([href^="javascript:"])',
                    getAttrs(dom: Element): object {
                        return {
                            href: dom.getAttribute('href'),
                            title: dom.getAttribute('title'),
                        };
                    }
                }
            ],
            toDOM(node: ProsemirrorNode): (string | number | object)[] {
                const { href, title, spellcheck } = node.attrs;
                const rel = 'noopener noreferrer';
                return ['a', { href, title, spellcheck, rel, }, 0];
            }
        };
    }
}
