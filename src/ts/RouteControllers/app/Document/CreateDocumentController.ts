import BreadcrumbList from 'components/BreadcrumbList';
import Controller from 'RouteControllers/Controller';
import TextEditor from 'text-editor/TextEditor';
import HttpClient from 'Http/HttpClient';

const keyupDelayMilliseconds = 1000;

export default class CreateDocumentController implements Controller {
    private readonly httpClient: HttpClient;
    private readonly userAgentProfile: Record<string, any> | null;
    private knowledgebaseId: string | null = null;
    private topicId: string | null = null;
    private allowedMarks: string[] = [];
    private allowedNodes: string[] = [];
    private formElement!: HTMLFormElement;
    private nameInput!: HTMLTextAreaElement;
    private descriptionInput!: HTMLTextAreaElement;
    private doneButton!: HTMLButtonElement;
    private textEditorElement!: HTMLDivElement;
    private textEditor!: TextEditor;
    private toolbar!: HTMLDivElement;
    private breadcrumbList!: BreadcrumbList;
    private nameKeyupTimer: number | null = null;

    constructor(
        httpClient: HttpClient,
        userAgentProfile: Record<string, any> | null,
    ) {
        this.httpClient = httpClient;
        this.userAgentProfile = userAgentProfile;
    }

    public start(): void {
        this.cacheElements();
        this.attachEvents();
        this.setUpTextEditor();
    }

    private cacheElements(): void {
        this.toolbar = <HTMLDivElement> document.querySelector('.js-document-toolbar');
        this.formElement = <HTMLFormElement> document.querySelector('.js-document-form');

        this.nameInput = <HTMLTextAreaElement> this.formElement.querySelector('[name="document_name"]');
        this.descriptionInput = <HTMLTextAreaElement> this.formElement.querySelector('[name="document_description"]');

        this.doneButton = <HTMLButtonElement> document.querySelector('.js-submit-document');
        this.textEditorElement = <HTMLDivElement> document.querySelector('.js-text-editor');

        const knowledgebaseIdInput = this.formElement.querySelector('[name="knowledgebase_id"]');
        if (!(knowledgebaseIdInput instanceof HTMLInputElement)) {
            throw new Error('Knowledgebase ID element not found');
        }

        const topicIdInput = this.formElement.querySelector('[name="topic_id"]');
        if (!(topicIdInput instanceof HTMLInputElement)) {
            throw new Error('Topic ID element not found');
        }

        const allowedMarksInput = this.formElement.querySelector('[name="allowed_marks"]');
        if (!(allowedMarksInput instanceof HTMLInputElement)) {
            throw new Error('Allowed marks element not found');
        }

        const allowedNodesInput = this.formElement.querySelector('[name="allowed_nodes"]');
        if (!(allowedNodesInput instanceof HTMLInputElement)) {
            throw new Error('Allowed nodes element not found');
        }

        const breadcrumbList = this.toolbar.querySelector('.js-breadcrumb-list');
        if (!(breadcrumbList instanceof BreadcrumbList)) {
            throw new Error('Breadcrumb list element not found');
        }

        this.knowledgebaseId = knowledgebaseIdInput.value;
        this.topicId = (topicIdInput.value !== '') ? topicIdInput.value : null;
        this.allowedMarks = JSON.parse(allowedMarksInput.value);
        this.allowedNodes = JSON.parse(allowedNodesInput.value);
        this.breadcrumbList = breadcrumbList;
    }

    private attachEvents(): void {
        this.nameInput.addEventListener('keyup', this.onNameKeyup.bind(this));
        this.nameInput.addEventListener('change', this.onNameChange.bind(this));
    }

    private onNameKeyup(): void {
        this.clearKeyupTimeout();
        this.nameKeyupTimer = window.setTimeout(this.updateDocName.bind(this), keyupDelayMilliseconds);
    }

    private onNameChange(): void {
        this.clearKeyupTimeout();
        this.updateDocName();
    }

    private updateDocName(): void {
        if (this.nameInput.value.length === 0) {
            this.breadcrumbList.revertActiveBreadcrumbText();
            return;
        }

        this.breadcrumbList.setActiveBreadcrumbText(this.nameInput.value);
    }

    private setUpTextEditor(): void {
        // eslint-disable-next-line @typescript-eslint/func-call-spacing
        import(/* webpackChunkName: "editor" */ 'text-editor/TextEditor').then(module => {
            this.textEditor = new module.default( // eslint-disable-line new-cap
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
            topic_id: this.topicId,
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

    private clearKeyupTimeout(): void {
        if (null !== this.nameKeyupTimer) {
            window.clearTimeout(this.nameKeyupTimer);
        }
    }
}
