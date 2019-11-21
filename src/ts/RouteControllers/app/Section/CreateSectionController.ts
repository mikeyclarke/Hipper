import HttpClient from 'Http/HttpClient';
import { HTTPError } from 'ky';
import showFieldError from 'Validation/showFieldError';

export default class CreateDocumentController {
    private readonly httpClient: HttpClient;
    private knowledgebaseId: string | null = null;
    private parentSectionId: string | null = null;
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
        this.formElement = <HTMLFormElement> document.querySelector('.js-create-section-form');
        this.submitButton = <HTMLButtonElement> this.formElement.querySelector('.js-submit');
        this.nameInput = <HTMLTextAreaElement> this.formElement.querySelector('[name="name"]');
        this.descriptionInput = <HTMLTextAreaElement> this.formElement.querySelector('[name="description"]');

        const knowledgebaseIdInput = this.formElement.querySelector('[name="knowledgebase_id"]');
        if (!(knowledgebaseIdInput instanceof HTMLInputElement)) {
            throw new Error('Knowledgebase ID element not found');
        }

        const parentSectionIdInput = this.formElement.querySelector('[name="parent_section_id"]');
        if (!(parentSectionIdInput instanceof HTMLInputElement)) {
            throw new Error('Parent section ID element not found');
        }

        this.knowledgebaseId = knowledgebaseIdInput.value;
        this.parentSectionId = (parentSectionIdInput.value !== '') ? parentSectionIdInput.value : null;
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
        this.createSection(payload)
            .then((sectionUrl) => {
                window.location.assign(sectionUrl);
            })
            .catch((error) => {
                if (error instanceof HTTPError) {
                    this.handleError(error);
                }
            })
            .finally(() => {
                this.submitButton.disabled = false;
            });
    }

    private handleError(error: HTTPError): void {
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
            parent_section_id: this.parentSectionId,
        };
    }

    private async createSection(payload: object): Promise<string> {
        const endpoint = '/_/create-section';

        const response = await this.httpClient.post(endpoint, {
            json: payload,
        });
        const json = await response.json();
        return json.section_url;
    }
}
