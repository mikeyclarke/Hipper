import BalloonEditor from '@ckeditor/ckeditor5-editor-balloon/src/ballooneditor';
import Essentials from '@ckeditor/ckeditor5-essentials/src/essentials';
import Paragraph from '@ckeditor/ckeditor5-paragraph/src/paragraph';
import Bold from '@ckeditor/ckeditor5-basic-styles/src/bold';
import Italic from '@ckeditor/ckeditor5-basic-styles/src/italic';

class ArticleEditor {
    private ckEditorInstance: BalloonEditor;
    private config: object = {
        plugins: [
            Essentials,
            Bold,
            Italic,
        ],
        toolbar: ['bold', 'italic'],
    };

    constructor(element: HTMLElement) {
        this.ckEditorInstance = BalloonEditor.create(element, this.config);
    }
}

export default ArticleEditor;
