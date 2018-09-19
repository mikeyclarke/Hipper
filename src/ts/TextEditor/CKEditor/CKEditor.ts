import BalloonEditor from '@ckeditor/ckeditor5-editor-balloon/src/ballooneditor';
import Autoformat from '@ckeditor/ckeditor5-autoformat/src/autoformat';
import BlockQuote from '@ckeditor/ckeditor5-block-quote/src/blockquote';
import Bold from '@ckeditor/ckeditor5-basic-styles/src/bold';
import Essentials from '@ckeditor/ckeditor5-essentials/src/essentials';
import Heading from '@ckeditor/ckeditor5-heading/src/heading';
import Highlight from '@ckeditor/ckeditor5-highlight/src/highlight';
import Italic from '@ckeditor/ckeditor5-basic-styles/src/italic';
import Link from '@ckeditor/ckeditor5-link/src/link';
import List from '@ckeditor/ckeditor5-list/src/list';
import Paragraph from '@ckeditor/ckeditor5-paragraph/src/paragraph';
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
