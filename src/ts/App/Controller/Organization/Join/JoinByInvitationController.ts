import FormSubmitHelper from 'Helper/FormSubmitHelper';
import PopoverAlert from 'components/PopoverAlert';
import ky from 'ky';

const ENDPOINT = '/_/join/by-invitation';

export default class JoinByInvitationController {
    private readonly formSubmitHelper: FormSubmitHelper;
    private formElement!: HTMLFormElement;
    private inviteIdInput!: HTMLInputElement;
    private inviteTokenInput!: HTMLInputElement;
    private nameInput!: HTMLInputElement;
    private passwordInput!: HTMLInputElement;
    private termsCheckbox!: HTMLInputElement;
    private submitButton!: HTMLButtonElement;

    constructor(
        formSubmitHelper: FormSubmitHelper
    ) {
        this.formSubmitHelper = formSubmitHelper;
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
        this.inviteIdInput = <HTMLInputElement> this.formElement.querySelector('[name="invite_id"]');
        this.inviteTokenInput = <HTMLInputElement> this.formElement.querySelector('[name="invite_token"]');
        this.nameInput = <HTMLInputElement> this.formElement.querySelector('[name="name"]');
        this.passwordInput = <HTMLInputElement> this.formElement.querySelector('[name="password"]');
        this.termsCheckbox = <HTMLInputElement> this.formElement.querySelector('[name="terms_agreed"]');
        this.submitButton = <HTMLButtonElement> this.formElement.querySelector('.js-submit');
    }

    private attachEvents(): void {
        this.formElement.addEventListener('submit', this.handleSubmit.bind(this));
        this.formElement.addEventListener('input', this.checkSubmitEligibility.bind(this));
    }

    private checkSubmitEligibility(): void {
        let eligible = false;

        if (this.nameInput.value.length &&
            this.passwordInput.value.length &&
            this.termsCheckbox.checked
        ) {
            eligible = true;
        }

        this.submitButton.disabled = !eligible;
    }

    private composePayload(): object {
        return {
            invite_id: this.inviteIdInput.value,
            invite_token: this.inviteTokenInput.value,
            name: this.nameInput.value,
            password: this.passwordInput.value,
            terms_agreed: this.termsCheckbox.checked,
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
                        case 'invite_not_found':
                            this.displayError(
                                'Invite not found',
                                'Please ask someone in your organization to send you a new invite'
                            );
                            break;
                        case 'invite_expired':
                            this.displayError(
                                'Invite expired',
                                'Please ask someone in your organization to send you a new invite'
                            );
                            break;
                        case 'email_address_taken':
                            this.displayError(
                                'Email address taken',
                                'Your email address is already in use'
                            );
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
}
