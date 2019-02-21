export function injectValidationErrors(form: HTMLElement, target: string, errors: string[]): void {
    const containerSelector = `[data-validation-error-container=${target}`;
    const validationContainerEl = <HTMLElement> form.querySelector(containerSelector);
    errors.forEach((error: string) => {
        const validationError = createValidationErrorElement(error);
        validationContainerEl.appendChild(validationError);
    });
}

function createValidationErrorElement(error: string): HTMLElement {
    const newErrorEl = document.createElement('p');
    newErrorEl.classList.add('c-signup-form__field-error', 'js-form-error');
    newErrorEl.innerText = error;
    return newErrorEl;
}