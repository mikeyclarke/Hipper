import IBootstrap from './IBootstrap';
import TextEditor from '../TextEditor/TextEditor';

export default class ApplicationBootstrap implements IBootstrap
{
    private textEditor;

    constructor(textEditor: TextEditor)
    {
        this.textEditor = textEditor;
    }

    public bootstrap(): void
    {
        require('Sass/app.scss');
        this.textEditor.initialiseEditor(document.querySelector('.js-article-editor'));
    }
}
