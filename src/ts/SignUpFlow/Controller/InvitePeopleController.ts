import PopoverAlert from 'components/PopoverAlert';
import HttpClient from 'Http/HttpClient';
import ky from 'ky';

const ENDPOINT = '/_/sign-up/invite-people';

export default class InvitePeople {
    private readonly httpClient: HttpClient;
    private formElement!: HTMLFormElement;
    private submitButton!: HTMLButtonElement;
    private approvedSignUpCheckbox!: HTMLInputElement | null;
    private approvedEmailDomainInput!: HTMLInputElement | null;

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
        this.formElement = <HTMLFormElement> document.querySelector('.js-invites-form');
        this.submitButton = <HTMLButtonElement> this.formElement.querySelector('.js-submit');
        this.approvedSignUpCheckbox = this.formElement.querySelector('[name="approved_email_domain_signup_allowed"]');
        this.approvedEmailDomainInput = this.formElement.querySelector('[name="approved_email_domain"]');
    }

    private attachEvents(): void {
        this.formElement.addEventListener('submit', this.handleSubmit.bind(this));
    }

    private async handleSubmit(event: Event): Promise<void> {
        event.preventDefault();

        this.submitButton.disabled = true;

        const payload = this.composePayload();
        try {
            const url = await this.makeRequest(payload);
            window.location.assign(url);
        } catch (error) {
            this.handleError(error);
        }

        this.submitButton.disabled = false;
    }

    private composePayload(): object {
        const payload: Record<string, any> = {};

        const inviteElements = this.formElement.querySelectorAll('[name="invite_email_address"]');
        if (inviteElements.length) {
            payload.email_invites = [];
            inviteElements.forEach((element) => {
                if (element instanceof HTMLInputElement) {
                    payload.email_invites.push(element.value);
                }
            });
        }

        if (!(this.approvedSignUpCheckbox instanceof HTMLInputElement) ||
            !this.approvedSignUpCheckbox.checked ||
            !(this.approvedEmailDomainInput instanceof HTMLInputElement)
        ) {
            return payload;
        }

        payload.approved_email_domain_signup_allowed = true;
        payload.approved_email_domains = [this.approvedEmailDomainInput.value];

        return payload;
    }

    private async makeRequest(payload: object): Promise<string> {
        const response = await this.httpClient.post(ENDPOINT, {
            json: payload,
        });
        const json = await response.json();
        return json.url;
    }

    private async handleError(error: Error): Promise<void> {
        if (error instanceof ky.HTTPError) {
            const response = <Response> error.response;
            if (response.status === 400) {
                const title = 'Something went wrong';
                const message = 'If the issue persists please get in touch with our support team';
                const popoverAlert = <PopoverAlert> document.createElement('popover-alert');
                popoverAlert.setAttribute('alert-title', title);
                popoverAlert.setAttribute('alert-message', message);
                popoverAlert.setAttribute('alert-type', 'error');
                document.body.appendChild(popoverAlert);
            }
        }
    }
}
