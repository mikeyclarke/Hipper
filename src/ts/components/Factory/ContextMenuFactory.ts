import ContextMenu from 'components/ContextMenu';

const CONTEXT_MENU_CLASSNAME = 'c-context-menu';
const CONTEXT_MENU_INNER_CLASSNAME = 'c-context-menu__content';
const CONTEXT_MENU_CLOSE_BUTTON_CLASSNAME = 'c-context-menu__close-button js-close-button';
const CONTEXT_MENU_CLOSE_BUTTON_LABEL = 'Close';

export default class ContextMenuFactory {
    public create(id: string, align: string = 'right'): ContextMenu {
        const element = <ContextMenu> document.createElement('context-menu');

        element.id = id;
        element.className = CONTEXT_MENU_CLASSNAME;
        if (align === 'left') {
            element.className += '--align-left';
        }
        element.setAttribute('role', 'menu');
        element.setAttribute('tabindex', '-1');
        element.setAttribute('aria-hidden', 'true');

        const inner = document.createElement('div');
        inner.className = CONTEXT_MENU_INNER_CLASSNAME;
        element.appendChild(inner);

        const closeButton = document.createElement('button');
        closeButton.type = 'button';
        closeButton.className = CONTEXT_MENU_CLOSE_BUTTON_CLASSNAME;
        closeButton.setAttribute('role', 'menuitem');
        closeButton.textContent = CONTEXT_MENU_CLOSE_BUTTON_LABEL;
        inner.appendChild(closeButton);

        return element;
    }
}
