import HttpClient from 'Http/HttpClient';
import ky from 'ky';
import showFieldError from 'Validation/showFieldError';

export default class JoinOrganizationController {
    private readonly httpClient: HttpClient;
    private formElement!: HTMLFormElement;
    private submitButton!: HTMLButtonElement;
    private emailLocalPartInput!: HTMLInputElement;
    private emailDomainInput!: HTMLInputElement | HTMLSelectElement;
    private nameInput!: HTMLInputElement;
    private passwordInput!: HTMLInputElement;
    private termsCheckbox!: HTMLInputElement;

    constructor(httpClient: HttpClient) {
        this.httpClient = httpClient;
    }

    public start(): void {
        const formElement = document.querySelector('.js-join-form');
        if (!(formElement instanceof HTMLFormElement)) {
            return;
        }

        this.formElement = formElement;

        this.cacheElements();
        this.attachEvents();
    }

    private cacheElements(): void {
        this.submitButton = <HTMLButtonElement> this.formElement.querySelector('.js-submit');
        this.emailLocalPartInput = <HTMLInputElement> this.formElement.querySelector('[name="email_local_part"]');
        this.emailDomainInput =
            <HTMLInputElement | HTMLSelectElement> this.formElement.querySelector('[name="email_domain"]');
        this.nameInput = <HTMLInputElement> this.formElement.querySelector('[name="name"]');
        this.passwordInput = <HTMLInputElement> this.formElement.querySelector('[name="password"]');
        this.termsCheckbox = <HTMLInputElement> this.formElement.querySelector('[name="terms_agreed"]');
    }

    private attachEvents(): void {
        this.formElement.addEventListener('submit', this.handleSubmit.bind(this));
        this.formElement.addEventListener('input', this.checkSubmitEligibility.bind(this));
    }

    private checkSubmitEligibility(): void {
        let eligible = false;

        if (this.nameInput.value.length &&
            this.emailLocalPartInput.value.length &&
            this.passwordInput.value.length >= 8 &&
            this.termsCheckbox.checked
        ) {
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
                    if (fieldName === 'email_address') {
                        fieldName = 'email_local_part';
                    }

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
            email_local_part: this.emailLocalPartInput.value,
            email_domain: this.emailDomainInput.value,
            password: this.passwordInput.value,
            terms_agreed: this.termsCheckbox.checked,
        };
    }

    private async submitRequest(payload: object): Promise<string> {
        const endpoint = '/_/join';

        const response = await this.httpClient.post(endpoint, {
            json: payload,
        });
        const json = await response.json();
        return json.url;
    }
}
