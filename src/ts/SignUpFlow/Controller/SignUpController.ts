import HttpClient from 'Http/HttpClient';
import ky from 'ky';
import showFieldError from 'Validation/showFieldError';

export default class SignUpController {
    private readonly httpClient: HttpClient;
    private formElement!: HTMLFormElement;
    private nameInput!: HTMLInputElement;
    private emailInput!: HTMLInputElement;
    private passwordInput!: HTMLInputElement;
    private termsCheckbox!: HTMLInputElement;
    private submitButton!: HTMLButtonElement;

    constructor(httpClient: HttpClient) {
        this.httpClient = httpClient;
    }

    public start(): void {
        this.cacheElements();
        this.attachEvents();
    }

    private cacheElements(): void {
        this.formElement = <HTMLFormElement> document.querySelector('.js-sign-up-form');
        this.nameInput = <HTMLInputElement> this.formElement.querySelector('[name="name"]');
        this.emailInput = <HTMLInputElement> this.formElement.querySelector('[name="email_address"]');
        this.passwordInput = <HTMLInputElement> this.formElement.querySelector('[name="password"]');
        this.termsCheckbox = <HTMLInputElement> this.formElement.querySelector('[name="terms_agreed"]');
        this.submitButton = <HTMLButtonElement> this.formElement.querySelector('.js-submit');
    }

    private attachEvents(): void {
        this.formElement.addEventListener('input', this.checkSubmitEligibility.bind(this));
        this.formElement.addEventListener('submit', this.handleSubmit.bind(this));
    }

    private checkSubmitEligibility(): void {
        const eligible = this.canSubmit();
        this.submitButton.disabled = !eligible;
    }

    private canSubmit(): boolean {
        return this.nameInput.value.length > 0 &&
            this.emailInput.value.length > 0 &&
            this.passwordInput.value.length >= 8 &&
            this.termsCheckbox.checked;
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
            name: this.nameInput.value,
            email_address: this.emailInput.value,
            password: this.passwordInput.value,
            terms_agreed: this.termsCheckbox.checked,
        };
    }

    private async submitRequest(payload: object): Promise<string> {
        const endpoint = '/_/sign-up';

        const response = await this.httpClient.post(endpoint, {
            json: payload,
        });
        const json = await response.json();
        return json.url;
    }
}
