import HttpClient from 'Http/HttpClient';
import ky from 'ky';
import showFieldError from 'Validation/showFieldError';

export default class JoinOrganizationController {
    private readonly httpClient: HttpClient;
    private formElement!: HTMLFormElement;
    private submitButton!: HTMLButtonElement;
    private phraseInput!: HTMLInputElement;

    constructor(httpClient: HttpClient) {
        this.httpClient = httpClient;
    }

    public start(): void {
        this.cacheElements();
        this.attachEvents();
    }

    private cacheElements(): void {
        this.formElement = <HTMLFormElement> document.querySelector('.js-verify-form');
        this.submitButton = <HTMLButtonElement> this.formElement.querySelector('.js-submit');
        this.phraseInput = <HTMLInputElement> this.formElement.querySelector('[name="phrase"]');
    }

    private attachEvents(): void {
        this.formElement.addEventListener('submit', this.handleSubmit.bind(this));
        this.formElement.addEventListener('input', this.checkSubmitEligibility.bind(this));
    }

    private checkSubmitEligibility(): void {
        let eligible = false;

        if (this.phraseInput.value.length) {
            eligible = true;
        }

        this.submitButton.disabled = !eligible;
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
        this.submitRequest(payload)
            .then((url) => {
                window.location.assign(url);
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
            phrase: this.phraseInput.value,
        };
    }

    private async submitRequest(payload: object): Promise<string> {
        const endpoint = '/_/join/verify-email-address';

        const response = await this.httpClient.post(endpoint, {
            json: payload,
        });
        const json = await response.json();
        return json.url;
    }
}
