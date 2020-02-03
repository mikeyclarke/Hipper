import Controller from 'RouteControllers/Controller';
import showFieldError from 'Validation/showFieldError';
import HttpClient from 'Http/HttpClient';
import ky from 'ky';

export default class CreateProjectController implements Controller {
    private readonly httpClient: HttpClient;
    private formElement!: HTMLFormElement;
    private submitButton!: HTMLButtonElement;
    private nameInput!: HTMLInputElement;
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
        this.formElement.addEventListener('submit', this.handleSubmit.bind(this));
    }

    private cacheElements(): void {
        this.formElement = <HTMLFormElement> document.querySelector('.js-create-project-form');
        this.submitButton = <HTMLButtonElement> this.formElement.querySelector('.js-submit');

        this.nameInput = <HTMLInputElement> this.formElement.querySelector('.js-project-name');
        this.descriptionInput = <HTMLTextAreaElement> this.formElement.querySelector('.js-project-description');
    }

    private handleSubmit(event: Event): void {
        event.preventDefault();
        if (this.formElement.querySelectorAll('[aria-invalid="true"]').length > 0) {
            const firstError = <HTMLElement> this.formElement.querySelector('[aria-invalid="true"]');
            firstError.focus();
            return;
        }

        this.submitButton.disabled = true;

        this.createProject(this.nameInput.value, this.descriptionInput.value)
            .then((projectUrl) => {
                window.location.assign(projectUrl);
            })
            .catch((error) => {
                if (error instanceof ky.HTTPError) {
                    this.handleError(error);
                }
            })
            .finally(() => {
                this.submitButton.disabled = false;
            });
    }

    private handleError(error: InstanceType<typeof ky.HTTPError>): void {
        const response = <Response> error.response;
        if (response.status !== 400) {
            return;
        }

        response.json().then((json) => {
            if (json.name === 'invalid_request_payload' && json.violations) {
                Object.entries(json.violations).forEach(([fieldName, errorMessage]) => {
                    const fieldInput = <HTMLElement> this.formElement.querySelector(`[name="${fieldName}"]`);
                    showFieldError(fieldInput, <string> errorMessage);
                });
                const firstError = <HTMLElement> this.formElement.querySelector('[aria-invalid="true"]');
                firstError.focus();
            }
        });
    }

    private async createProject(projectName: string, projectDescription: string): Promise<string> {
        const endpoint = '/_/create-project';

        const response = await this.httpClient.post(endpoint, {
            json: {
                name: projectName,
                description: projectDescription,
            },
        });
        const json = await response.json();
        return json.project_url;
    }
}
