const OFFSET_TO_INPUT_TOP = '6px';

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
    errorElement.style.bottom = `calc(100% - ${fieldInputElement.offsetTop}px + ${OFFSET_TO_INPUT_TOP})`;

    fieldInputElement.setAttribute('aria-invalid', 'true');
    fieldInputElement.setAttribute('aria-errormessage', id);

    const inputEvent = 'input';

    const onInput = (): void => {
        errorElement.remove();
        fieldInputElement.setAttribute('aria-invalid', 'false');
        fieldInputElement.removeEventListener(inputEvent, onInput);
    };
    fieldInputElement.addEventListener(inputEvent, onInput);

    fieldInputElement.insertAdjacentElement('afterend', errorElement);
}
