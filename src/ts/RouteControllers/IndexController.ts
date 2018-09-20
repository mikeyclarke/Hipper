import { IController } from './IController';
import { TextEditor } from '../TextEditor/TextEditor';

export class IndexController implements IController {
    private readonly textEditor: TextEditor;

    constructor(textEditor: TextEditor) {
        this.textEditor = textEditor;
    }

    public start(): void {
        require('Sass/app.scss');
        this.textEditor.initialiseEditor(document.querySelector('.js-article-editor'));
    }
}
