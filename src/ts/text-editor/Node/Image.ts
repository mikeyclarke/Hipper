import { Node as ProseMirrorNode } from 'prosemirror-model';
import { NodeInterface } from 'text-editor/Node/NodeInterface';

export class Image implements NodeInterface {
    get name(): string {
        return 'image';
    }

    get spec(): object {
        return {
            inline: true,
            attrs: {
                src: {},
                alt: {
                    default: null
                },
                title: {
                    default: null
                }
            },
            group: 'inline',
            draggable: true,
            parseDOM: [
                {
                    tag: 'img[src]',
                    getAttrs(dom: HTMLElement): object {
                        return {
                            src: dom.getAttribute('src'),
                            title: dom.getAttribute('title'),
                            alt: dom.getAttribute('alt')
                        };
                    }
                }
            ],
            toDOM(node: ProseMirrorNode): (string | object)[] {
                const { src, alt, title } = node.attrs;
                return ['img', { src, alt, title }];
            }
        };
    }
}
