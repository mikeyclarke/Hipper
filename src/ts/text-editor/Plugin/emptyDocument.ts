import { EditorState, Plugin } from 'prosemirror-state';
import { Decoration, DecorationSet } from 'prosemirror-view';
import { Node as ProseMirrorNode } from 'prosemirror-model';

export function emptyDocument(): Plugin {
    return new Plugin({
        props: {
            decorations(state: EditorState): DecorationSet {
                const decorations: Decoration[] = [];
                const decorate = function(node: ProseMirrorNode, position: number): void {
                    if (node.type.isBlock && node.childCount === 0) {
                        decorations.push(
                            Decoration.node(position, position + node.nodeSize, { class: 'is-empty' })
                        );
                    }
                };

                state.doc.descendants(decorate);

                return DecorationSet.create(state.doc, decorations);
            },
        },
    });
}
