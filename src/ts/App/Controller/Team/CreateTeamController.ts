import EditableFormField from 'components/EditableFormField';
import FormSubmitHelper from 'Helper/FormSubmitHelper';

const SUGGEST_ENDPOINT = '/_/suggest-team-description';
const CREATE_ENDPOINT = '/_/create-team';

export default class CreateTeamController {
    private readonly formSubmitHelper: FormSubmitHelper;
    private handleNameSubmitHandler!: EventListener;
    private formElement!: HTMLFormElement;
    private submitButton!: HTMLButtonElement;
    private nameField!: EditableFormField;
    private nameInput!: HTMLInputElement;
    private descriptionField!: EditableFormField;
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
        this.handleNameSubmitHandler = this.handleNameSubmit.bind(this);
        this.formElement.addEventListener('submit', this.handleNameSubmitHandler);
    }

    private cacheElements(): void {
        this.formElement = <HTMLFormElement> document.querySelector('.js-create-team-form');
        this.submitButton = <HTMLButtonElement> this.formElement.querySelector('.js-submit');

        this.nameField = <EditableFormField> this.formElement.querySelector('.js-team-name-field');
        this.nameInput = <HTMLInputElement> this.nameField.querySelector('.js-team-name');

        this.descriptionField = <EditableFormField> this.formElement.querySelector('.js-team-description-field');
        this.descriptionInput = <HTMLTextAreaElement> this.descriptionField.querySelector('.js-team-description');
    }

    private composeNamePayload(): object {
        return {
            name: this.nameInput.value,
        };
    }

    private async handleNameSubmit(event: Event): Promise<void> {
        event.preventDefault();

        if (null !== document.activeElement) {
            const activeElement = <HTMLElement> document.activeElement;
            activeElement.blur();
        }

        const payload = this.composeNamePayload();
        const json = await this.formSubmitHelper.submit(this.formElement, this.submitButton, SUGGEST_ENDPOINT, payload);
        if (null !== json) {
            const suggestedDescription = json.suggested_description;

            this.descriptionField.classList.remove('is-invisible');
            this.descriptionField.setAttribute('aria-hidden', 'false');

            if (null !== suggestedDescription) {
                this.descriptionInput.value = suggestedDescription;
                this.descriptionField.editable = false;
                this.nameField.editable = false;
            } else {
                this.descriptionInput.focus();
            }

            this.formElement.removeEventListener('submit', this.handleNameSubmitHandler);
            this.formElement.addEventListener('submit', this.handleFullSubmit.bind(this));
            this.submitButton.textContent = 'Done';
        }
    }

    private composeFullPayload(): object {
        return {
            name: this.nameInput.value,
            description: this.descriptionInput.value,
        };
    }

    private async handleFullSubmit(event: Event): Promise<void> {
        event.preventDefault();

        const payload = this.composeFullPayload();
        const json = await this.formSubmitHelper.submit(this.formElement, this.submitButton, CREATE_ENDPOINT, payload);
        if (null !== json) {
            window.location.assign(json.team_url);
        }
    }
}
