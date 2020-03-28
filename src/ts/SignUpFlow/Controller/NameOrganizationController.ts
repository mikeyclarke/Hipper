import FormSubmitHelper from 'Helper/FormSubmitHelper';

const ENDPOINT = '/_/sign-up/name-organization';

export default class NameOrganizationController {
    private readonly formSubmitHelper: FormSubmitHelper;
    private formElement!: HTMLFormElement;
    private nameInput!: HTMLInputElement;
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
        this.formElement = <HTMLFormElement> document.querySelector('.js-name-organization-form');
        this.nameInput = <HTMLInputElement> this.formElement.querySelector('[name="name"]');
        this.submitButton = <HTMLButtonElement> this.formElement.querySelector('.js-submit');
    }

    private attachEvents(): void {
        this.formElement.addEventListener('input', this.checkSubmitEligibility.bind(this));
        this.formElement.addEventListener('submit', this.handleSubmit.bind(this));
    }

    private checkSubmitEligibility(): void {
        const eligible = this.nameInput.value.length > 0;
        this.submitButton.disabled = !eligible;
    }

    private composePayload(): object {
        return {
            name: this.nameInput.value,
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
