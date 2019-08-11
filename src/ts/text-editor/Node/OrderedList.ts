import { Node as ProsemirrorNode } from 'prosemirror-model';
import { NodeInterface } from 'text-editor/Node/NodeInterface';

export class OrderedList implements NodeInterface {
    get name(): string {
        return 'ordered_list';
    }

    get spec(): object {
        return {
            attrs: {
                order: {
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
                            order: 1
                        };

                        if (!dom.hasAttribute('start')) {
                            return attrs;
                        }

                        const attributeValue = Number(dom.getAttribute('start'));
                        if (!Number.isNaN(attributeValue)) {
                            attrs.order = attributeValue;
                        }

                        return attrs;
                    }
                }
            ],
            toDOM(node: ProsemirrorNode): (string | number | object)[] {
                return (node.attrs.order === 1) ? ['ol', 0] : ['ol', { start: node.attrs.order }, 0];
            }
        };
    }
}
