import { EventDelegator } from '../../hleo/EventDelegator/EventDelegator';
import { ElementCache } from '../../hleo/ElementCache/ElementCache';
import { submitSignup } from './SignupService';
import { IEvents } from '../../hleo/EventDelegator/IEvents';
import { IElementHash } from 'hleo/ElementCache/IElementHash';
import { IEventEnabled } from '../../hleo/EventDelegator/IEventEnabled';

export class SignupForm implements IEventEnabled {
    private isPasswordVisible: boolean = false;
    private readonly eventDelegator: EventDelegator;
    private readonly elementCache: ElementCache;

    private readonly events: IEvents = {
        keyup: 'onFormInteraction',
        change: 'onFormInteraction',
        submit: 'onSubmit',
        'click .js-toggle-password-visibility': 'onTogglePasswordVisibilityClick',
    };

    public static readonly elements: IElementHash = {
        form: '.js-signup-form',
        togglePasswordVisibilityButton: '.js-toggle-password-visibility',
        submitButton: '.js-form-submit',
        passwordInputElement: '.js-password-input',
    };

    constructor(eventDelegator: EventDelegator, elementCache: ElementCache) {
        this.eventDelegator = eventDelegator;
        this.elementCache = elementCache;
    }

    public init(): void {
        this.eventDelegator.setContext(this);
        this.eventDelegator.setEvents(this.events);
        this.eventDelegator.delegate();
    }

    protected onSubmit(event: Event): void {
        event.preventDefault();
        submitSignup((res: Response) => {
            // console.log(res);
        }, this.getFormData());
    }

    public getEvents(): IEvents {
        return this.events;
    }

    protected onFormInteraction(): void {
        const form = <HTMLFormElement> this.elementCache.get('form');
        if (form.checkValidity()) {
            this.elementCache.get('submitButton').setAttribute('aria-disabled', 'false');
        } else {
            this.elementCache.get('submitButton').setAttribute('aria-disabled', 'true');
        }
    }

    protected onTogglePasswordVisibilityClick(): void {
        if (this.isPasswordVisible) {
            this.setPasswordFieldType('password');
            this.setTogglePasswordVisibilityText('Show');
            this.isPasswordVisible = false;
        } else {
            this.setPasswordFieldType('text');
            this.setTogglePasswordVisibilityText('Hide');
            this.isPasswordVisible = true;
        }
        this.elementCache.get('passwordInputElement').focus();
    }

    private getFormData(): FormData {
        return new FormData(<HTMLFormElement> this.elementCache.get('form'));
    }

    private setTogglePasswordVisibilityText(text: string): void {
        this.elementCache.get('togglePasswordVisibilityButton').innerText = text;
    }

    private setPasswordFieldType(type: string): void {
        const passwordField = <HTMLInputElement> this.elementCache.get('passwordInputElement');
        passwordField.type = type;
    }
}
