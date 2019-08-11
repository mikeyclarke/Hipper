import { Plugin } from 'prosemirror-state';
import { EditorView } from 'prosemirror-view';
import { DetachableMenuView } from 'text-editor/View/DetachableMenuView';

export function detachableMenu(commands: Record<string, object>, options: {}): Plugin {
    return new Plugin({
        view(editorView: EditorView): DetachableMenuView {
            const menuView = new DetachableMenuView(commands, editorView, options);
            const layoutContainerElement = document.querySelector('.js-document-editor-container');
            if (null === layoutContainerElement) {
                throw new Error('Couldn’t find document editor container element');
            }
            layoutContainerElement.appendChild(menuView.element);
            return menuView;
        }
    });
}
