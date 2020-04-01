import FormSubmitHelper from 'Helper/FormSubmitHelper';
import PopoverAlert from 'components/PopoverAlert';
import timeout from 'Timeout/timeout';
import ky from 'ky';

const ENDPOINT = '/_/sign-up/verify-email-address';

export default class VerifyEmailAddressController {
    private readonly formSubmitHelper: FormSubmitHelper;
    private formElement!: HTMLFormElement;
    private phraseInput!: HTMLInputElement;
    private submitButton!: HTMLButtonElement;

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
        this.formElement = <HTMLFormElement> document.querySelector('.js-verify-form');
        this.phraseInput = <HTMLInputElement> this.formElement.querySelector('[name="phrase"]');
        this.submitButton = <HTMLButtonElement> this.formElement.querySelector('.js-submit');
    }

    private attachEvents(): void {
        this.formElement.addEventListener('input', this.checkSubmitEligibility.bind(this));
        this.formElement.addEventListener('submit', this.handleSubmit.bind(this));
    }

    private checkSubmitEligibility(): void {
        const eligible = this.phraseInput.value.length;
        this.submitButton.disabled = !eligible;
    }

    private composePayload(): object {
        return {
            phrase: this.phraseInput.value,
        };
    }

    private async handleSubmit(event: Event): Promise<void> {
        event.preventDefault();

        const payload = this.composePayload();
        let json = null;

        try {
            json = await this.formSubmitHelper.submit(this.formElement, this.submitButton, ENDPOINT, payload);
        } catch (error) {
            this.handleError(error);
        }

        if (null !== json) {
            window.location.assign(json.url);
        }
    }

    private async handleError(error: Error): Promise<void> {
        if (error instanceof ky.HTTPError) {
            const response = <Response> error.response;
            if (response.status === 400) {
                const json = await response.json();
                if (json.name) {
                    switch (json.name) {
                        case 'sign_up_auth_request_not_found':
                            this.displayError(
                                'Verification expired',
                                'Your verification request has expired, please start the sign-up process again'
                            );
                            this.redirect();
                            break;
                        case 'email_address_taken':
                            this.displayError(
                                'Email address taken',
                                'Your email address is now already in use, please try signing-up again'
                            );
                            this.redirect();
                            break;
                        default:
                    }
                }
            }
        }
    }

    private displayError(title: string, message: string): void {
        const popoverAlert = <PopoverAlert> document.createElement('popover-alert');
        popoverAlert.setAttribute('alert-title', title);
        popoverAlert.setAttribute('alert-message', message);
        popoverAlert.setAttribute('alert-type', 'error');
        document.body.appendChild(popoverAlert);
    }

    private async redirect(): Promise<void> {
        await timeout(1500);
        window.location.assign('/sign-up');
    }
}
