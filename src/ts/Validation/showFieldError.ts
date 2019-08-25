export default function showFieldError(
    fieldInputElement: HTMLElement,
    errorMessage: string,
    className: string = 'c-form__error'
): void {
    const errorElement = document.createElement('span');
    const id = fieldInputElement.id + '-error';
    errorElement.id = id;
    errorElement.textContent = errorMessage;
    errorElement.classList.add(className);
    errorElement.setAttribute('aria-live', 'assertive');

    fieldInputElement.setAttribute('aria-invalid', 'true');
    fieldInputElement.setAttribute('aria-errormessage', id);

    // Edge only supports the input event on input[type=text] and input[type=password]
    // https://developer.mozilla.org/en-US/docs/Web/API/HTMLElement/input_event#Browser_compatibility
    let inputEvent = 'input';
    if (!(fieldInputElement instanceof HTMLInputElement) || !['text', 'password'].includes(fieldInputElement.type)) {
        inputEvent = 'keyup';
    }

    const onInput = () => {
        errorElement.remove();
        fieldInputElement.setAttribute('aria-invalid', 'false');
        fieldInputElement.removeEventListener(inputEvent, onInput);
    };
    fieldInputElement.addEventListener(inputEvent, onInput);

    fieldInputElement.insertAdjacentElement('afterend', errorElement);
}
