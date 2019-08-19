import { Node as ProseMirrorNode } from 'prosemirror-model';
import { NodeInterface } from 'text-editor/Node/NodeInterface';

export class OrderedList implements NodeInterface {
    get name(): string {
        return 'ordered_list';
    }

    get spec(): object {
        return {
            attrs: {
                start: {
                    default: 1
                }
            },
            content: 'list_item+',
            group: 'block',
            parseDOM: [
                {
                    tag: 'ol',
                    getAttrs(dom: HTMLElement): object {
                        const attrs = {
                            start: 1
                        };

                        if (!dom.hasAttribute('start')) {
                            return attrs;
                        }

                        const attributeValue = Number(dom.getAttribute('start'));
                        if (!Number.isNaN(attributeValue)) {
                            attrs.start = attributeValue;
                        }

                        return attrs;
                    }
                }
            ],
            toDOM(node: ProseMirrorNode): (string | number | object)[] {
                return (node.attrs.start === 1) ? ['ol', 0] : ['ol', { start: node.attrs.start }, 0];
            }
        };
    }
}
