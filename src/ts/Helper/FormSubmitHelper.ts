import HttpClient from 'Http/HttpClient';
import ky from 'ky';
import showFieldError from 'Validation/showFieldError';

export default class FormSubmitHelper {
    private readonly httpClient: HttpClient;

    constructor(
        httpClient: HttpClient
    ) {
        this.httpClient = httpClient;
    }

    public async submit(
        formElement: HTMLFormElement,
        submitButton: HTMLButtonElement,
        endpoint: string,
        payload: object,
        errorKeyReplacements: Record<string, string> = {}
    ): Promise<Record<string, any> | null> {
        if (formElement.querySelectorAll('[aria-invalid="true"]').length > 0) {
            const firstError = <HTMLElement> formElement.querySelector('[aria-invalid="true"]');
            firstError.focus();
            return null;
        }

        submitButton.disabled = true;

        let result = null;
        try {
            result = await this.submitRequest(endpoint, payload);
        } catch (error) {
            submitButton.disabled = false;

            if (error instanceof ky.HTTPError) {
                const responseCopy = error.response.clone();
                this.handleError(formElement, errorKeyReplacements, error);
                error.response = responseCopy;
            }
            throw error;
        }

        submitButton.disabled = false;

        return result;
    }

    private handleError(
        formElement: HTMLFormElement,
        errorKeyReplacements: Record<string, string>,
        error: InstanceType<typeof ky.HTTPError>
    ): void {
        const response = <Response> error.response;
        if (response.status !== 400) {
            return;
        }

        response.json().then((json) => {
            if (json.name === 'invalid_request_payload' && json.violations) {
                Object.entries(json.violations).forEach(([fieldName, errorMessage]) => {
                    if (errorKeyReplacements[fieldName]) {
                        fieldName = errorKeyReplacements[fieldName];
                    }
                    const fieldInput = <HTMLElement> formElement.querySelector(`[name="${fieldName}"]`);
                    showFieldError(fieldInput, <string> errorMessage);
                });
                const firstError = <HTMLElement> formElement.querySelector('[aria-invalid="true"]');
                firstError.focus();
            }
        });
    }

    private async submitRequest(endpoint: string, payload: object): Promise<object> {
        const response = await this.httpClient.post(endpoint, {
            json: payload,
        });
        const json = await response.json();
        return json;
    }
}
