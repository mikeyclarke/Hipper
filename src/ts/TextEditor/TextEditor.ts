import { ITextEditor } from './ITextEditor';

export class TextEditor {
    private readonly editor: ITextEditor;
    constructor(editor: ITextEditor) {
        this.editor = editor;
    }

    public initialiseEditor(element: HTMLElement): void {
        this.editor.initialiseEditor(element);
    }
}
