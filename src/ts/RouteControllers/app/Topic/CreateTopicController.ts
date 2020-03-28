import FormSubmitHelper from 'Helper/FormSubmitHelper';

const ENDPOINT = '/_/create-topic';

export default class CreateDocumentController {
    private readonly formSubmitHelper: FormSubmitHelper;
    private knowledgebaseId: string | null = null;
    private parentTopicId: string | null = null;
    private formElement!: HTMLFormElement;
    private submitButton!: HTMLButtonElement;
    private nameInput!: HTMLTextAreaElement;
    private descriptionInput!: HTMLTextAreaElement;

    constructor(
        formSubmitHelper: FormSubmitHelper
    ) {
        this.formSubmitHelper = formSubmitHelper;
    }

    public start(): void {
        this.cacheElements();
        this.attachEvents();
    }

    private cacheElements(): void {
        this.formElement = <HTMLFormElement> document.querySelector('.js-create-topic-form');
        this.submitButton = <HTMLButtonElement> this.formElement.querySelector('.js-submit');
        this.nameInput = <HTMLTextAreaElement> this.formElement.querySelector('[name="name"]');
        this.descriptionInput = <HTMLTextAreaElement> this.formElement.querySelector('[name="description"]');

        const knowledgebaseIdInput = this.formElement.querySelector('[name="knowledgebase_id"]');
        if (!(knowledgebaseIdInput instanceof HTMLInputElement)) {
            throw new Error('Knowledgebase ID element not found');
        }

        const parentTopicIdInput = this.formElement.querySelector('[name="parent_topic_id"]');
        if (!(parentTopicIdInput instanceof HTMLInputElement)) {
            throw new Error('Parent topic ID element not found');
        }

        this.knowledgebaseId = knowledgebaseIdInput.value;
        this.parentTopicId = (parentTopicIdInput.value !== '') ? parentTopicIdInput.value : null;
    }

    private attachEvents(): void {
        this.formElement.addEventListener('submit', this.handleSubmit.bind(this));
    }

    private composePayload(): object {
        return {
            name: this.nameInput.value,
            description: this.descriptionInput.value,
            knowledgebase_id: this.knowledgebaseId,
            parent_topic_id: this.parentTopicId,
        };
    }

    private async handleSubmit(event: Event): Promise<void> {
        event.preventDefault();

        const payload = this.composePayload();
        const json = await this.formSubmitHelper.submit(this.formElement, this.submitButton, ENDPOINT, payload);
        if (null !== json) {
            window.location.assign(json.topic_url);
        }
    }
}
