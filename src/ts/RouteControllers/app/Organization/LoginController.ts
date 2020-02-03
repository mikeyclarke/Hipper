import HttpClient from 'Http/HttpClient';
import PopoverAlert from 'components/PopoverAlert';
import ky from 'ky';
import showFieldError from 'Validation/showFieldError';

export default class LoginController {
    private readonly httpClient: HttpClient;
    private formElement!: HTMLFormElement;
    private submitButton!: HTMLButtonElement;
    private emailInput!: HTMLInputElement;
    private passwordInput!: HTMLInputElement;
    private redirect: string | null = null;

    constructor(
        httpClient: HttpClient
    ) {
        this.httpClient = httpClient;
    }

    public start(): void {
        this.cacheElements();

        const redirectInput = this.formElement.querySelector('[name="redirect"]');
        if (redirectInput instanceof HTMLInputElement) {
            this.redirect = redirectInput.value || null;
        }

        this.attachEvents();
    }

    private cacheElements(): void {
        this.formElement = <HTMLFormElement> document.querySelector('.js-login-form');
        this.submitButton = <HTMLButtonElement> this.formElement.querySelector('.js-submit');
        this.emailInput = <HTMLInputElement> this.formElement.querySelector('[name="email_address"]');
        this.passwordInput = <HTMLInputElement> this.formElement.querySelector('[name="password"]');
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
        this.login(payload)
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

            if (json.name === 'invalid_credentials') {
                const title = 'We couldn’t sign you in';
                const message = 'Check that your email address and password are correct.';
                const popoverAlert = <PopoverAlert> document.createElement('popover-alert');
                popoverAlert.setAttribute('alert-title', title);
                popoverAlert.setAttribute('alert-message', message);
                popoverAlert.setAttribute('alert-type', 'error');
                document.body.appendChild(popoverAlert);
            }
        });
    }

    private composePayload(): object {
        return {
            email_address: this.emailInput.value,
            password: this.passwordInput.value,
            redirect: this.redirect,
        };
    }

    private async login(payload: object): Promise<string> {
        const endpoint = '/_/login';

        const response = await this.httpClient.post(endpoint, {
            json: payload,
        });
        const json = await response.json();
        return json.url;
    }
}
