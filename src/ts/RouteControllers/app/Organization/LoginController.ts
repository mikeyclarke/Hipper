import PopoverAlert from 'components/PopoverAlert';
import FormSubmitHelper from 'Helper/FormSubmitHelper';
import ky from 'ky';

const ENDPOINT = '/_/login';

export default class LoginController {
    private readonly formSubmitHelper: FormSubmitHelper;
    private formElement!: HTMLFormElement;
    private submitButton!: HTMLButtonElement;
    private emailInput!: HTMLInputElement;
    private passwordInput!: HTMLInputElement;
    private redirect: string | null = null;

    constructor(
        formSubmitHelper: FormSubmitHelper
    ) {
        this.formSubmitHelper = formSubmitHelper;
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

    private composePayload(): object {
        return {
            email_address: this.emailInput.value,
            password: this.passwordInput.value,
            redirect: this.redirect,
        };
    }

    private async handleSubmit(event: Event): Promise<void> {
        event.preventDefault();

        let json = null;
        const payload = this.composePayload();
        try {
            json = await this.formSubmitHelper.submit(this.formElement, this.submitButton, ENDPOINT, payload);
        } catch (error) {
            this.handleIncorrectCredentials(error);
        }

        if (null !== json) {
            window.location.assign(json.url);
        }
    }

    private async handleIncorrectCredentials(error: Error): Promise<void> {
        if (error instanceof ky.HTTPError) {
            const response = <Response> error.response;
            if (response.status === 400) {
                const json = await response.json();
                if (json.name === 'invalid_credentials') {
                    const title = 'We couldn’t sign you in';
                    const message = 'Check that your email address and password are correct.';
                    const popoverAlert = <PopoverAlert> document.createElement('popover-alert');
                    popoverAlert.setAttribute('alert-title', title);
                    popoverAlert.setAttribute('alert-message', message);
                    popoverAlert.setAttribute('alert-type', 'error');
                    document.body.appendChild(popoverAlert);
                }
            }
        }
    }
}
