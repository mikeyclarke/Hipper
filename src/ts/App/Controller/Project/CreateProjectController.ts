import FormSubmitHelper from 'Helper/FormSubmitHelper';

const ENDPOINT = '/_/create-project';

export default class CreateProjectController {
    private readonly formSubmitHelper: FormSubmitHelper;
    private formElement!: HTMLFormElement;
    private submitButton!: HTMLButtonElement;
    private nameInput!: HTMLInputElement;
    private descriptionInput!: HTMLTextAreaElement;

    constructor(
        formSubmitHelper: FormSubmitHelper
    ) {
        this.formSubmitHelper = formSubmitHelper;
    }

    public start(): void {
        this.cacheElements();
        this.attachEvents();
    }

    private attachEvents(): void {
        this.formElement.addEventListener('submit', this.handleSubmit.bind(this));
    }

    private cacheElements(): void {
        this.formElement = <HTMLFormElement> document.querySelector('.js-create-project-form');
        this.submitButton = <HTMLButtonElement> this.formElement.querySelector('.js-submit');

        this.nameInput = <HTMLInputElement> this.formElement.querySelector('.js-project-name');
        this.descriptionInput = <HTMLTextAreaElement> this.formElement.querySelector('.js-project-description');
    }

    private composePayload(): object {
        return {
            name: this.nameInput.value,
            description: this.descriptionInput.value,
        };
    }

    private async handleSubmit(event: Event): Promise<void> {
        event.preventDefault();

        const payload = this.composePayload();
        const json = await this.formSubmitHelper.submit(this.formElement, this.submitButton, ENDPOINT, payload);
        if (null !== json) {
            window.location.assign(json.project_url);
        }
    }
}
