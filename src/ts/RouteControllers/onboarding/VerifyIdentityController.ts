import Controller from 'RouteControllers/Controller';
import VerifyIdentityForm from 'onboarding/VerifyIdentityForm/VerifyIdentityForm';
import ElementCache from 'hleo/ElementCache/ElementCache';
import EventDelegator from 'hleo/EventDelegator/EventDelegator';
import Form from 'onboarding/Form/Form';
import FormSubmitService from 'onboarding/Form/FormSubmitService';
import HttpClient from 'Http/HttpClient';

export default class VerifyIdentityController implements Controller {
    private readonly httpClient: HttpClient;
    private verifyIdentityForm!: VerifyIdentityForm;

    constructor(
        httpClient: HttpClient
    ) {
        this.httpClient = httpClient;
    }

    public start(): void {
        const elementCache = new ElementCache('.js-verify-identity-form', VerifyIdentityForm.elements);
        const eventDelegator = new EventDelegator(elementCache.get('form'));
        const formEl = <HTMLFormElement> elementCache.get('form');
        const submitEl = elementCache.get('submitButton');
        const submitService = new FormSubmitService(this.httpClient, 200, '/_/verify-identity');
        const form = new Form(formEl, submitEl);
        this.verifyIdentityForm = new VerifyIdentityForm(eventDelegator, elementCache, form, submitService);
        this.verifyIdentityForm.init();
    }
}
