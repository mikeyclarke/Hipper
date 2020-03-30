import FormSubmitHelper from 'Helper/FormSubmitHelper';

const ENDPOINT = '/_/sign-up';

export default class SignUpController {
    private readonly formSubmitHelper: FormSubmitHelper;
    private formElement!: HTMLFormElement;
    private organizationNameInput!: HTMLInputElement;
    private nameInput!: HTMLInputElement;
    private emailInput!: HTMLInputElement;
    private passwordInput!: HTMLInputElement;
    private termsCheckbox!: HTMLInputElement;
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
        this.formElement = <HTMLFormElement> document.querySelector('.js-sign-up-form');
        this.organizationNameInput = <HTMLInputElement> this.formElement.querySelector('[name="organization_name"]');
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
        return this.organizationNameInput.value.length > 0 &&
            this.nameInput.value.length > 0 &&
            this.emailInput.value.length > 0 &&
            this.passwordInput.value.length >= 8 &&
            this.termsCheckbox.checked;
    }

    private composePayload(): object {
        return {
            organization_name: this.organizationNameInput.value,
            name: this.nameInput.value,
            email_address: this.emailInput.value,
            password: this.passwordInput.value,
            terms_agreed: this.termsCheckbox.checked,
        };
    }

    private async handleSubmit(event: Event): Promise<void> {
        event.preventDefault();

        const payload = this.composePayload();
        const json = await this.formSubmitHelper.submit(this.formElement, this.submitButton, ENDPOINT, payload);
        if (null !== json) {
            window.location.assign(json.url);
        }
    }
}
