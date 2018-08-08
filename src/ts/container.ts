import * as Bottle from 'bottlejs';
import CKeditor from './CKEditor/CKEditor';
import TextEditor from './Library/TextEditor/TextEditor';

const bottle = new Bottle();

bottle.factory('CKEditor', () => {
    return new CKeditor();
});

bottle.factory('textEditor', (container) => {
    return new TextEditor(container.CKEditor);
});

export default bottle.container;
