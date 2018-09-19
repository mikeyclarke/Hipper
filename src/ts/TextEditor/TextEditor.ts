import ITextEditor from './ITextEditor';

export default class TextEditor {
    private editor: ITextEditor;
    constructor(editor: ITextEditor) {
        this.editor = editor;
    }

    public initialiseEditor(element: HTMLElement): void {
        this.editor.initialiseEditor(element);
    }
}
