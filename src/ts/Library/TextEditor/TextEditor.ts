import ITextEditor from "./ITextEditor";

let editor;
export default class TextEditor
{
    constructor(editor: ITextEditor)
    {
        editor = editor;
    }

    public initialiseEditor(element: HTMLElement): void
    {
        editor.initialiseEditor(element);
    }
}
