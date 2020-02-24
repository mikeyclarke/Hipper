import HttpClient from 'Http/HttpClient';
import ky from 'ky';
import showFieldError from 'Validation/showFieldError';

export default class CreateDocumentController {
    private readonly httpClient: HttpClient;
    private knowledgebaseId: string | null = null;
    private parentTopicId: string | null = null;
    private formElement!: HTMLFormElement;
    private submitButton!: HTMLButtonElement;
    private nameInput!: HTMLTextAreaElement;
    private descriptionInput!: HTMLTextAreaElement;

    constructor(
        httpClient: HttpClient
    ) {
        this.httpClient = httpClient;
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

    private handleSubmit(event: Event): void {
        event.preventDefault();
        if (this.formElement.querySelectorAll('[aria-invalid="true"]').length > 0) {
            const firstError = <HTMLElement> this.formElement.querySelector('[aria-invalid="true"]');
            firstError.focus();
            return;
        }

        this.submitButton.disabled = true;

        const payload = this.composePayload();
        this.createTopic(payload)
            .then((topicUrl) => {
                window.location.assign(topicUrl);
            })
            .catch((error) => {
                if (error instanceof ky.HTTPError) {
                    this.handleError(error);
                }
            })
            .finally(() => {
                this.submitButton.disabled = false;
            });
    }

    private handleError(error: InstanceType<typeof ky.HTTPError>): void {
        const response = <Response> error.response;
        if (response.status !== 400) {
            return;
        }

        response.json().then((json) => {
            if (json.name === 'invalid_request_payload' && json.violations) {
                Object.entries(json.violations).forEach(([fieldName, errorMessage]) => {
                    const fieldInput = <HTMLElement> this.formElement.querySelector(`[name="${fieldName}"]`);
                    showFieldError(fieldInput, <string> errorMessage);
                });
                const firstError = <HTMLElement> this.formElement.querySelector('[aria-invalid="true"]');
                firstError.focus();
            }
        });
    }

    private composePayload(): object {
        return {
            name: this.nameInput.value,
            description: this.descriptionInput.value,
            knowledgebase_id: this.knowledgebaseId,
            parent_topic_id: this.parentTopicId,
        };
    }

    private async createTopic(payload: object): Promise<string> {
        const endpoint = '/_/create-topic';

        const response = await this.httpClient.post(endpoint, {
            json: payload,
        });
        const json = await response.json();
        return json.topic_url;
    }
}
