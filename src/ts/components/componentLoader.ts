import { PasswordInput } from 'components/PasswordInput';
import { EditableFormField } from 'components/EditableFormField';
import { PopoverAlert } from 'components/PopoverAlert';
import { FloatingButton } from 'components/FloatingButton';
import { ElasticTextInput } from 'components/ElasticTextInput';

export function loadComponents(): void {
    window.customElements.define('password-input', PasswordInput);
    window.customElements.define('editable-form-field', EditableFormField);
    window.customElements.define('popover-alert', PopoverAlert);
    window.customElements.define('floating-button', FloatingButton);
    window.customElements.define('elastic-text-input', ElasticTextInput);
}
