import { Controller } from './Controller';
import { TextEditor } from '../TextEditor/TextEditor';

export class IndexController implements Controller {
    private readonly textEditor: TextEditor;

    constructor(textEditor: TextEditor) {
        this.textEditor = textEditor;
    }

    public start(): void {
        require('Sass/app.scss');
        const textEditorNode = <HTMLElement> document.querySelector('.js-article-editor');
        if (textEditorNode) {
            this.textEditor.initialiseEditor(textEditorNode);
        }
    }
}
