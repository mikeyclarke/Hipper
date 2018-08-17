import IBootstrap from './IBootstrap';
import { Navigation } from '../UIControls/Navigation/Navigation';
import TextEditor from '../Library/TextEditor/TextEditor';

export default class ApplicationBootstrap implements IBootstrap
{
    private NavigationControl;
    private textEditor;

    constructor(NavigationControl: Navigation, textEditor: TextEditor)
    {
        this.NavigationControl = NavigationControl;
        this.textEditor = textEditor;
    }

    public bootstrap(): void
    {
        require('Sass/app.scss');
        const navigation = new this.NavigationControl('.js-navigation-container');
        this.textEditor.initialiseEditor(document.querySelector('.js-article-editor'));
    }
}
