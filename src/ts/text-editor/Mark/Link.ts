import { Node as ProseMirrorNode } from 'prosemirror-model';
import MarkInterface from 'text-editor/Mark/MarkInterface';

export default class Link implements MarkInterface {
    get name(): string {
        return 'link';
    }

    get spec(): object {
        return {
            attrs: {
                href: {},
                title: {
                    default: null,
                },
                spellcheck: {
                    default: null,
                },
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
                    },
                },
            ],
            toDOM(node: ProseMirrorNode): (string | number | object)[] {
                const { href, title, spellcheck } = node.attrs;
                const rel = 'noopener noreferrer';
                const htmlAttributes = { href, title, spellcheck, rel };

                try {
                    const url = new URL(href, window.location.href);
                    if (url.origin === window.location.origin) {
                        delete htmlAttributes.rel;
                    }
                } catch (error) {
                }

                return ['a', htmlAttributes, 0];
            },
        };
    }
}
