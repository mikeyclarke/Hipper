import FormSubmitHelper from 'Helper/FormSubmitHelper';

const ENDPOINT = '/_/join/verify-email-address';

export default class JoinOrganizationController {
    private readonly formSubmitHelper: FormSubmitHelper;
    private formElement!: HTMLFormElement;
    private submitButton!: HTMLButtonElement;
    private phraseInput!: HTMLInputElement;

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
        this.submitButton = <HTMLButtonElement> this.formElement.querySelector('.js-submit');
        this.phraseInput = <HTMLInputElement> this.formElement.querySelector('[name="phrase"]');
    }

    private attachEvents(): void {
        this.formElement.addEventListener('submit', this.handleSubmit.bind(this));
        this.formElement.addEventListener('input', this.checkSubmitEligibility.bind(this));
    }

    private checkSubmitEligibility(): void {
        let eligible = false;

        if (this.phraseInput.value.length) {
            eligible = true;
        }

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
        const json = await this.formSubmitHelper.submit(this.formElement, this.submitButton, ENDPOINT, payload);
        if (null !== json) {
            window.location.assign(json.url);
        }
    }
}
