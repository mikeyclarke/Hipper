import { PasswordInput } from './PasswordInput';

export function loadComponents(): void {
    window.customElements.define('password-input', PasswordInput);
}