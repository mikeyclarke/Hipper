require('Sass/app.scss');
import container from './container';

document.addEventListener('DOMContentLoaded', () => {
    container.textEditor.initialiseEditor(document.querySelector('.js-article-editor'));
    var nav = new container.navigationControl('.js-navigation-container');
});
