import Controller from 'RouteControllers/Controller';
import TextEditor from 'text-editor/TextEditor';
import HttpClient from 'Http/HttpClient';
import { HTTPError } from 'ky';

export default class CreateDocumentController implements Controller {
    private readonly httpClient: HttpClient;
    private readonly userAgentProfile: Record<string, any> | null;
    private knowledgebaseId: string | null = null;
    private sectionId: string | null = null;
    private allowedMarks: string[] = [];
    private allowedNodes: string[] = [];
    private formElement!: HTMLFormElement;
    private nameInput!: HTMLTextAreaElement;
    private descriptionInput!: HTMLTextAreaElement;
    private doneButton!: HTMLButtonElement;
    private textEditorElement!: HTMLDivElement;
    private textEditor!: TextEditor;

    constructor(
        httpClient: HttpClient,
        userAgentProfile: Record<string, any> | null,
    ) {
        this.httpClient = httpClient;
        this.userAgentProfile = userAgentProfile;
    }

    public start(): void {
        this.cacheElements();
        this.setUpTextEditor();
    }

    private cacheElements(): void {
        this.formElement = <HTMLFormElement> document.querySelector('.js-document-form');

        this.nameInput = <HTMLTextAreaElement> this.formElement.querySelector('[name="document_name"]');
        this.descriptionInput = <HTMLTextAreaElement> this.formElement.querySelector('[name="document_description"]');

        this.doneButton = <HTMLButtonElement> document.querySelector('.js-submit-document');
        this.textEditorElement = <HTMLDivElement> document.querySelector('.js-text-editor');

        const knowledgebaseIdInput = this.formElement.querySelector('[name="knowledgebase_id"]');
        if (!(knowledgebaseIdInput instanceof HTMLInputElement)) {
            throw new Error('Knowledgebase ID element not found');
        }

        const sectionIdInput = this.formElement.querySelector('[name="section_id"]');
        if (!(sectionIdInput instanceof HTMLInputElement)) {
            throw new Error('Section ID element not found');
        }

        const allowedMarksInput = this.formElement.querySelector('[name="allowed_marks"]');
        if (!(allowedMarksInput instanceof HTMLInputElement)) {
            throw new Error('Allowed marks element not found');
        }

        const allowedNodesInput = this.formElement.querySelector('[name="allowed_nodes"]');
        if (!(allowedNodesInput instanceof HTMLInputElement)) {
            throw new Error('Allowed nodes element not found');
        }

        this.knowledgebaseId = knowledgebaseIdInput.value;
        this.sectionId = (sectionIdInput.value !== '') ? sectionIdInput.value : null;
        this.allowedMarks = JSON.parse(allowedMarksInput.value);
        this.allowedNodes = JSON.parse(allowedNodesInput.value);
    }

    private setUpTextEditor(): void {
        import (/* webpackChunkName: "editor" */ 'text-editor/TextEditor').then(module => {
            this.textEditor = new module.default(
                this.textEditorElement,
                '',
                this.allowedMarks,
                this.allowedNodes,
                this.userAgentProfile,
            );

            this.doneButton.addEventListener('click', async () => {
                const payload = this.composePayload();
                const docUrl = await this.createDoc(payload);
                window.location.assign(docUrl);
            });
        });
    }

    private composePayload(): object {
        return {
            name: this.nameInput.value,
            description: this.descriptionInput.value,
            content: this.textEditor.getContent(),
            knowledgebase_id: this.knowledgebaseId,
            section_id: this.sectionId,
        };
    }

    private async createDoc(payload: object): Promise<string> {
        const endpoint = '/_/create-doc';

        const response = await this.httpClient.post(endpoint, {
            json: payload,
        });
        const json = await response.json();
        return json.doc_url;
    }
}
