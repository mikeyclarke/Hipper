import FormSubmitHelper from 'Helper/FormSubmitHelper';

const ENDPOINT = '/_/sign-up';

export default class ChooseOrganizationUrlController {
    private readonly formSubmitHelper: FormSubmitHelper;
    private formElement!: HTMLFormElement;
    private subdomainInput!: HTMLInputElement;
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
        this.formElement = <HTMLFormElement> document.querySelector('.js-url-form');
        this.subdomainInput = <HTMLInputElement> this.formElement.querySelector('[name="subdomain"]');
        this.submitButton = <HTMLButtonElement> this.formElement.querySelector('.js-submit');
    }

    private attachEvents(): void {
        this.formElement.addEventListener('input', this.checkSubmitEligibility.bind(this));
        this.formElement.addEventListener('submit', this.handleSubmit.bind(this));
    }

    private checkSubmitEligibility(): void {
        const eligible = this.subdomainInput.value.length > 0;
        this.submitButton.disabled = !eligible;
    }

    private composePayload(): object {
        return {
            subdomain: this.subdomainInput.value,
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
