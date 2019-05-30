import { Controller } from '../Controller';
import { SignupForm } from '../../onboarding/SignupForm/SignupForm';
import { ElementCache } from 'hleo/ElementCache/ElementCache';
import { EventDelegator } from 'hleo/EventDelegator/EventDelegator';
import { Form } from 'onboarding/Form/Form';
import { FormSubmitService } from 'onboarding/Form/FormSubmitService';
import { HttpClient } from 'Http/HttpClient';

export class SignupController implements Controller {
    private readonly httpClient: HttpClient;
    private signupForm!: SignupForm;

    constructor(
        httpClient: HttpClient
    ) {
        this.httpClient = httpClient;
    }

    public start(): void {
        const elementCache = new ElementCache('.js-signup-form', SignupForm.elements);
        const eventDelegator = new EventDelegator(elementCache.get('form'));
        const form = new Form(<HTMLFormElement> elementCache.get('form'), elementCache.get('submitButton'));
        const submitService = new FormSubmitService(this.httpClient, 201, '/_/sign-up');
        this.signupForm = new SignupForm(eventDelegator, elementCache, form, submitService);
        this.signupForm.init();
    }
}
