import IBootstrap from './IBootstrap';
import { Navigation } from '../UIControls/Navigation/Navigation';
import TextEditor from '../Library/TextEditor/TextEditor';

export default class ApplicationBootstrap implements IBootstrap
{
    private navigationControl;
    private textEditor;

    constructor(navigationControl: Navigation, textEditor: TextEditor)
    {
        this.navigationControl = navigationControl;
        this.textEditor = textEditor;
    }

    public bootstrap(): void
    {
        require('Sass/app.scss');
        this.textEditor.initialiseEditor(document.querySelector('.js-article-editor'));
    }
}
