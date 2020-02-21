import HttpClient from 'Http/HttpClient';

const PAGE_HEADER_EDITING_CLASSNAME = 'is-editing-page-header';

export default class SectionController {
    private readonly httpClient: HttpClient;
    private header!: HTMLElement;
    private displayContainer: HTMLElement | null = null;
    private form: HTMLFormElement | null = null;
    private sectionId: string | null = null;

    constructor(
        httpClient: HttpClient
    ) {
        this.httpClient = httpClient;
    }

    public start(routeParameters: Map<string, string>): void {
        const header = document.querySelector('.js-knowledgebase-section-header');
        if (!(header instanceof HTMLElement)) {
            throw new Error('Section header element not found');
        }

        this.header = header;

        this.cacheElements();
        this.cacheSectionId();
        this.attachEvents();
    }

    private cacheElements(): void {
        this.displayContainer = this.header.querySelector('.js-section-details-display');
        this.form = this.header.querySelector('.js-section-details-edit-form');
    }

    private cacheSectionId(): void {
        if (null === this.form) {
            return;
        }

        const sectionIdElement = this.form.querySelector('[name="section_id"]');
        if (sectionIdElement instanceof HTMLInputElement) {
            this.sectionId = sectionIdElement.value;
        }
    }

    private attachEvents(): void {
        const eventMap: Record<string, Function> = {
            'js-edit-section': this.enterEditMode,
            'js-submit-edit-section': this.onSaveChangesButtonClick,
            'js-cancel-edit-section': this.onCancelChangesButtonClick,
        };

        this.header.addEventListener('click', (event: UIEvent) => {
            if (!(event.target instanceof Element)) {
                return;
            }

            for (const [className, handler] of Object.entries(eventMap)) {
                if (event.target.classList.contains(className)) {
                    handler.bind(this)();
                    return;
                }

                const closest = event.target.closest(`.${className}`);
                if (null !== closest) {
                    handler.bind(this)();
                    return;
                }
            }
        });
    }

    private rerender(html: string): void {
        const fragment = document.createElement('template');
        fragment.innerHTML = html;
        const newHeader = fragment.content.firstElementChild;
        if (null === newHeader) {
            return;
        }
        this.header.innerHTML = newHeader.innerHTML;

        this.cacheElements();
    }

    private async onSaveChangesButtonClick(): Promise<void> {
        if (null === this.sectionId || null === this.form) {
            throw new Error('Changes can’t be saved');
        }

        const payload: Record<string, string> = {};
        const name = this.form.elements.namedItem('name');
        const description = this.form.elements.namedItem('description');

        if (name instanceof HTMLTextAreaElement) {
            payload.name = name.value;
        }

        if (description instanceof HTMLTextAreaElement) {
            payload.description = description.value;
        }

        const responseJson: Record<string, string> = await this.updateSection(this.sectionId, payload);
        if (undefined === responseJson.section_url || undefined === responseJson.header_html) {
            throw new Error('Unexpected response format');
        }

        if (responseJson.section_url !== window.location.pathname) {
            window.history.replaceState(null, document.title, responseJson.section_url);
        }

        this.rerender(responseJson.header_html);
        this.exitEditMode();
    }

    private onCancelChangesButtonClick(): void {
        if (null === this.form) {
            return;
        }

        this.form.reset();
        this.exitEditMode();
    }

    private enterEditMode(): void {
        if (null === this.form || null === this.displayContainer) {
            throw new Error('Cannot open edit mode');
        }

        this.displayContainer.hidden = true;
        this.form.hidden = false;

        const firstTextArea = this.form.querySelector('textarea');
        if (null !== firstTextArea) {
            firstTextArea.focus();
            firstTextArea.selectionStart = firstTextArea.value.length;
            firstTextArea.selectionEnd = firstTextArea.value.length;
        }

        document.documentElement.classList.add(PAGE_HEADER_EDITING_CLASSNAME);
    }

    private exitEditMode(): void {
        if (null === this.form || null === this.displayContainer) {
            return;
        }

        this.form.hidden = true;
        this.displayContainer.hidden = false;

        document.documentElement.classList.remove(PAGE_HEADER_EDITING_CLASSNAME);
    }

    private async updateSection(sectionId: string, payload: object): Promise<Record<string, string>> {
        const endpoint = '/_/update-section/' + sectionId;

        const response = await this.httpClient.post(endpoint, {
            json: payload,
        });
        const json = await response.json();
        return json;
    }
}
