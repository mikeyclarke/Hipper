import HttpClient from 'Http/HttpClient';

const PAGE_HEADER_EDITING_CLASSNAME = 'is-editing-page-header';

export default class TopicController {
    private readonly httpClient: HttpClient;
    private header!: HTMLElement;
    private displayContainer: HTMLElement | null = null;
    private form: HTMLFormElement | null = null;
    private topicId: string | null = null;

    constructor(
        httpClient: HttpClient
    ) {
        this.httpClient = httpClient;
    }

    public start(routeParameters: Map<string, string>): void {
        const header = document.querySelector('.js-knowledgebase-topic-header');
        if (!(header instanceof HTMLElement)) {
            throw new Error('Topic header element not found');
        }

        this.header = header;

        this.cacheElements();
        this.cacheTopicId();
        this.attachEvents();
    }

    private cacheElements(): void {
        this.displayContainer = this.header.querySelector('.js-topic-details-display');
        this.form = this.header.querySelector('.js-topic-details-edit-form');
    }

    private cacheTopicId(): void {
        if (null === this.form) {
            return;
        }

        const topicIdElement = this.form.querySelector('[name="topic_id"]');
        if (topicIdElement instanceof HTMLInputElement) {
            this.topicId = topicIdElement.value;
        }
    }

    private attachEvents(): void {
        const eventMap: Record<string, Function> = {
            'js-edit-topic': this.enterEditMode,
            'js-submit-edit-topic': this.onSaveChangesButtonClick,
            'js-cancel-edit-topic': this.onCancelChangesButtonClick,
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
        if (null === this.topicId || null === this.form) {
            throw new Error('Changes can’t be saved');
        }

        const buttons = this.header.querySelectorAll('.js-submit-edit-topic, .js-cancel-edit-topic');
        const setButtonsDisabled = (value: boolean): void => {
            buttons.forEach((element) => {
                if (element instanceof HTMLButtonElement) {
                    element.disabled = value;
                }
            });
        };

        setButtonsDisabled(true);

        const payload: Record<string, string> = {};
        const name = this.form.elements.namedItem('name');
        const description = this.form.elements.namedItem('description');

        if (name instanceof HTMLTextAreaElement) {
            payload.name = name.value;
        }

        if (description instanceof HTMLTextAreaElement) {
            payload.description = description.value;
        }

        const responseJson: Record<string, string> = await this.updateTopic(this.topicId, payload);
        if (undefined === responseJson.topic_url || undefined === responseJson.header_html) {
            throw new Error('Unexpected response format');
        }

        if (responseJson.topic_url !== window.location.pathname) {
            window.history.replaceState(null, document.title, responseJson.topic_url);
        }

        setButtonsDisabled(false);

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

    private async updateTopic(topicId: string, payload: object): Promise<Record<string, string>> {
        const endpoint = '/_/update-topic/' + topicId;

        const response = await this.httpClient.post(endpoint, {
            json: payload,
        });
        const json = await response.json();
        return json;
    }
}
