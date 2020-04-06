import FormSubmitHelper from 'Helper/FormSubmitHelper';

const ENDPOINT = '/_/join';

export default class JoinOrganizationController {
    private readonly formSubmitHelper: FormSubmitHelper;
    private formElement!: HTMLFormElement;
    private submitButton!: HTMLButtonElement;
    private emailLocalPartInput!: HTMLInputElement;
    private emailDomainInput!: HTMLInputElement | HTMLSelectElement;
    private nameInput!: HTMLInputElement;
    private passwordInput!: HTMLInputElement;
    private termsCheckbox!: HTMLInputElement;

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
            this.passwordInput.value.length &&
            this.termsCheckbox.checked
        ) {
            eligible = true;
        }

        this.submitButton.disabled = !eligible;
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

    private async handleSubmit(event: Event): Promise<void> {
        event.preventDefault();

        const payload = this.composePayload();
        const fieldReplacements = { email_address: 'email_local_part' };
        const json = await this.formSubmitHelper.submit(
            this.formElement,
            this.submitButton,
            ENDPOINT,
            payload,
            fieldReplacements
        );
        if (null !== json) {
            window.location.assign(json.url);
        }
    }
}
