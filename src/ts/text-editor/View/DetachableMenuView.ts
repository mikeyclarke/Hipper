import { EditorView } from 'prosemirror-view';
import timeout from 'Timeout/timeout';

declare global {
    interface Window {
        visualViewport: {
            height: number;
            addEventListener: Function;
        };
    }
}

interface MenuOptions {
    detachable?: boolean;
    isDetached?: boolean;
    patchMobileSafari?: boolean;
}

const eventDelegationMap = new Map();

export default class DetachableMenuView {
    private readonly commandsWhiteList: string[] = [
        'strong',
        'emphasis',
        'strike',
        'ordered_list',
        'unordered_list',
        'link',
        'blockquote',
    ];
    private readonly layoutContainerElement: HTMLElement;
    private readonly editorView: EditorView;
    private readonly items: HTMLButtonElement[];
    private detachable: boolean = false;
    private isDetached: boolean = false;
    private isVisible: boolean = true;
    private patchMobileSafari: boolean = false;
    public element: HTMLDivElement;

    constructor(enabledCommands: Record<string, object>, editorView: EditorView, options: MenuOptions) {
        const containerElement = document.querySelector('.js-document-editor-container');
        if (null === containerElement) {
            throw new Error('Couldn’t find document editor container element');
        }

        this.layoutContainerElement = <HTMLElement> containerElement;

        this.editorView = editorView;
        this.setOptions(options);
        this.element = this.createMenuViewFragment();
        this.items = this.createItems(enabledCommands);

        if (!this.detachable) {
            this.layoutContainerElement.classList.add('is-static-layout');
        }

        this.attachEvents();
    }

    public update(): void {
        if (this.isDetached && this.isVisible && this.editorView.state.selection.empty) {
            this.element.hidden = true;
            this.isVisible = false;
        }

        this.updateUserInterface();
    }

    private setOptions(options: MenuOptions): void {
        if (options.patchMobileSafari) {
            this.patchMobileSafari = true;
        }

        if (!options.detachable) {
            this.isVisible = false;
            return;
        }

        this.detachable = true;

        if (options.isDetached === true) {
            this.isDetached = true;
            this.isVisible = false;
        }
    }

    private floatMenuOnClick(event: MouseEvent): void {
        if (this.element === event.target || (event.target instanceof Node && this.element.contains(event.target))) {
            return;
        }

        if (!this.editorView.hasFocus()) {
            this.element.hidden = true;
            this.isVisible = false;
            return;
        }

        if (this.editorView.state.selection.empty) {
            return;
        }

        this.isVisible = true;
        this.element.hidden = false;

        this.positionDetachedMenu(event.pageX, event.pageY);
    }

    private floatMenuOnKeyUp(event: KeyboardEvent): void {
        if (!['Shift', 'Meta', 'Control'].includes(event.key)) {
            return;
        }

        if (this.editorView.state.selection.empty) {
            return;
        }

        this.isVisible = true;
        this.element.hidden = false;

        const { from, to } = this.editorView.state.selection;
        const start = this.editorView.coordsAtPos(from);
        const end = this.editorView.coordsAtPos(to);

        const eventX = (start.left + end.right) / 2;
        const eventY = Math.max(start.bottom, end.bottom);

        this.positionDetachedMenu(eventX, eventY);
    }

    private positionDetachedMenu(eventX: number, eventY: number): void {
        const minimumLeftRightOffset = 16;
        const width = this.element.getBoundingClientRect().width;

        let offsetLeft = eventX - (width / 2);
        offsetLeft = Math.max(minimumLeftRightOffset, offsetLeft);
        offsetLeft = Math.min(window.innerWidth - width - minimumLeftRightOffset, offsetLeft);

        this.element.style.left = `${offsetLeft}px`;
        this.element.style.top = `${eventY + 20}px`;
    }

    private showMenuOnFocus(): void {
        this.element.hidden = false;
        this.isVisible = true;
    }

    private hideMenuOnBlur(): void {
        this.element.hidden = true;
        this.isVisible = false;
    }

    private updateUserInterface(): void {
        eventDelegationMap.forEach((command, element) => {
            if (!command.isAvailable(this.editorView)) {
                element.classList.remove('is-available', 'is-applied');
                element.setAttribute('aria-disabled', 'true');
                return;
            }

            element.classList.add('is-available');
            element.setAttribute('aria-disabled', 'false');

            if (command.isApplied(this.editorView)) {
                element.classList.add('is-applied');
                element.setAttribute('aria-pressed', 'true');
            } else {
                element.classList.remove('is-applied');
                element.setAttribute('aria-pressed', 'false');
            }
        });
    }

    private createMenuViewFragment(): HTMLDivElement {
        const element = document.createElement('div');
        element.classList.add('c-document-editor-menu');
        element.setAttribute('aria-role', 'toolbar');
        element.setAttribute('aria-label', 'Text formatting');

        if (this.isDetached) {
            element.classList.add('is-detached');
        }

        if (!this.detachable) {
            element.classList.add('is-static-layout');
        }

        if (!this.isVisible) {
            element.hidden = true;
        }

        return element;
    }

    private createItems(enabledCommands: Record<string, any>): HTMLButtonElement[] {
        const commands = Object.entries(enabledCommands);
        const items = [];

        for (const [name, properties] of commands) {
            if (!this.commandsWhiteList.includes(name)) {
                continue;
            }

            const button = document.createElement('button');
            button.setAttribute('aria-label', properties.label);
            button.setAttribute('aria-pressed', 'false');
            button.classList.add('c-document-editor-menu__button', 'is-available');
            button.innerHTML =
                '<svg class="c-document-editor-menu__button-icon" aria-hidden="true">' +
                '<use xlink:href="#icon-sprite__text-editor-' + name.replace('_', '-') + '"/>' +
                '</svg>';

            eventDelegationMap.set(button, properties.getCommand());
            this.element.appendChild(button);
            items.push(button);
        }

        return items;
    }

    private attachEvents(): void {
        // Mousedown fires ahead of click and—if preventDefault is invoked—stops the text editor from losing focus.
        this.element.addEventListener('mousedown', (event) => {
            event.preventDefault();

            if (eventDelegationMap.has(event.target)) {
                const command = eventDelegationMap.get(event.target);
                command.execute(this.editorView);
                return;
            }

            for (const [button, command] of eventDelegationMap) {
                if (button.contains(event.target)) {
                    command.execute(this.editorView);
                    break;
                }
            }
        });

        if (this.isDetached) {
            document.addEventListener('click', this.floatMenuOnClick.bind(this));
            const editorElement = <HTMLElement> this.editorView.dom;
            editorElement.addEventListener('keyup', this.floatMenuOnKeyUp.bind(this));
        }

        if (!this.detachable) {
            this.editorView.dom.addEventListener('focus', this.showMenuOnFocus.bind(this));
            this.editorView.dom.addEventListener('blur', this.hideMenuOnBlur.bind(this));
        }

        if (this.patchMobileSafari && 'visualViewport' in window) {
            window.visualViewport.addEventListener('resize', async () => {
                this.layoutContainerElement.style.height = `${window.visualViewport.height}px`;

                await timeout(200);

                window.scrollTo(0, 0);
            });

            window.addEventListener('scroll', preventWindowScroll);
        }

        if (this.patchMobileSafari && !('visualViewport' in window)) {
            const onFocus = async (): Promise<void> => {
                window.scrollTo(0, window.innerHeight);

                await timeout(200);

                const currentHeight = window.innerHeight;
                const visualViewport = currentHeight;

                this.layoutContainerElement.style.height = `${visualViewport}px`;
                window.scrollTo(0, 0);
                window.addEventListener('scroll', preventWindowScroll);
            };

            const onBlur = async (): Promise<void> => {
                await timeout(50);

                if (document.body !== document.activeElement) {
                    return;
                }

                this.layoutContainerElement.style.height = 'auto';
                this.layoutContainerElement.addEventListener('focusin', onFocus, { once: true });
                window.removeEventListener('scroll', preventWindowScroll);
            };

            this.layoutContainerElement.addEventListener('focusin', onFocus, { once: true });
            this.layoutContainerElement.addEventListener('focusout', onBlur);
        }
    }
}

function preventWindowScroll(event: Event): void {
    if (!(event.target instanceof HTMLElement)) {
        window.scrollTo(0, 0);
    }
}
