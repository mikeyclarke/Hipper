// @ts-ignore
import BalloonEditor from '@ckeditor/ckeditor5-editor-balloon/src/ballooneditor';
// @ts-ignore
import Autoformat from '@ckeditor/ckeditor5-autoformat/src/autoformat';
// @ts-ignore
import BlockQuote from '@ckeditor/ckeditor5-block-quote/src/blockquote';
// @ts-ignore
import Bold from '@ckeditor/ckeditor5-basic-styles/src/bold';
// @ts-ignore
import Essentials from '@ckeditor/ckeditor5-essentials/src/essentials';
// @ts-ignore
import Heading from '@ckeditor/ckeditor5-heading/src/heading';
// @ts-ignore
import Highlight from '@ckeditor/ckeditor5-highlight/src/highlight';
// @ts-ignore
import Italic from '@ckeditor/ckeditor5-basic-styles/src/italic';
// @ts-ignore
import Link from '@ckeditor/ckeditor5-link/src/link';
// @ts-ignore
import List from '@ckeditor/ckeditor5-list/src/list';
// @ts-ignore
import Paragraph from '@ckeditor/ckeditor5-paragraph/src/paragraph';
// @ts-ignore
import Strikethrough from '@ckeditor/ckeditor5-basic-styles/src/strikethrough';
import { ITextEditor } from '../ITextEditor';

export class CKeditor implements ITextEditor {
    private ckEditorInstance: BalloonEditor;
    private readonly config: object = {
        plugins: [
            Autoformat,
            BlockQuote,
            Bold,
            Essentials,
            Heading,
            Italic,
            Link,
            List,
            Paragraph,
            Strikethrough,
            Highlight,
        ],
        toolbar: ['bold', 'italic', 'strikethrough', 'blockQuote', 'link'],
    };

    public initialiseEditor(element: HTMLElement): void {
        this.ckEditorInstance = BalloonEditor.create(element, this.config);
    }
}
