import { Controller } from 'RouteControllers/Controller';
import { EditableFormField } from 'components/EditableFormField';
import { showFieldError } from 'Validation/showFieldError';
import { HttpClient } from 'Http/HttpClient';
import { HTTPError } from 'ky';

export class CreateTeamController implements Controller {
    private readonly httpClient: HttpClient;
    private handleNameSubmitHandler!: EventListener;
    private formElement!: HTMLFormElement;
    private submitButton!: HTMLButtonElement;
    private nameField!: EditableFormField;
    private nameInput!: HTMLInputElement;
    private descriptionField!: EditableFormField;
    private descriptionInput!: HTMLTextAreaElement;

    constructor(
        httpClient: HttpClient
    ) {
        this.httpClient = httpClient;
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

    private handleNameSubmit(event: Event): void {
        event.preventDefault();
        if (null !== document.activeElement) {
            const activeElement = <HTMLElement> document.activeElement;
            activeElement.blur();
        }

        this.submitButton.disabled = true;

        this.suggestDescription(this.nameInput.value).then((suggestedDescription) => {
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
        }).catch((error) => {
            if (error instanceof HTTPError) {
                this.handleError(error);
            }
        }).finally(() => {
            this.submitButton.disabled = false;
        });
    }

    private handleFullSubmit(event: Event): void {
        event.preventDefault();
        if (this.formElement.querySelectorAll('[aria-invalid="true"]').length > 0) {
            const firstError = <HTMLElement> this.formElement.querySelector('[aria-invalid="true"]');
            firstError.focus();
            return;
        }

        this.submitButton.disabled = true;

        this.createTeam(this.nameInput.value, this.descriptionInput.value)
            .then((teamUrl) => {
                window.location.assign(teamUrl);
            })
            .catch((error) => {
                if (error instanceof HTTPError) {
                    this.handleError(error);
                }
            })
            .finally(() => {
                this.submitButton.disabled = false;
            });
    }

    private handleError(error: HTTPError): void {
        const response = <Response> error.response;
        if (response.status !== 400) {
            return;
        }

        response.json().then((json) => {
            if (json.name === 'invalid_request_payload' && json.violations) {
                Object.entries(json.violations).forEach(([fieldName, errorMessage]) => {
                    const fieldInput = <HTMLElement> this.formElement.querySelector(`[name="${fieldName}"]`);
                    if (fieldInput.parentElement instanceof EditableFormField && !fieldInput.parentElement.editable) {
                        fieldInput.parentElement.editable = true;
                    }
                    showFieldError(fieldInput, <string> errorMessage);
                });
                const firstError = <HTMLElement> this.formElement.querySelector('[aria-invalid="true"]');
                firstError.focus();
            }
        });
    }

    private async createTeam(teamName: string, teamDescription: string): Promise<string> {
        const endpoint = '/_/create-team';

        const response = await this.httpClient.post(endpoint, {
            json: {
                name: teamName,
                description: teamDescription,
            },
        });
        const json = await response.json();
        return json.team_url;
    }

    private async suggestDescription(teamName: string): Promise<string> {
        const endpoint = '/_/suggest-team-description';

        const response = await this.httpClient.post(endpoint, {
            json: {
                name: teamName,
            },
        });
        const json = await response.json();
        return json.suggested_description;
    }
}
