import { PasswordInput } from 'components/PasswordInput';
import { EditableFormField } from 'components/EditableFormField';
import { PopoverAlert } from 'components/PopoverAlert';

export function loadComponents(): void {
    window.customElements.define('password-input', PasswordInput);
    window.customElements.define('editable-form-field', EditableFormField);
    window.customElements.define('popover-alert', PopoverAlert);
}
