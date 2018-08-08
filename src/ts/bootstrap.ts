require('Sass/app.scss');
import container from './container';
import { Navigation } from './UIControls/Navigation/Navigation';

document.addEventListener('DOMContentLoaded', () => {
    console.log(container.textEditor);
    container.textEditor.initialiseEditor(document.querySelector('.js-article-editor'));
    const nav = new Navigation('hello world1234');
    nav.render();
});
