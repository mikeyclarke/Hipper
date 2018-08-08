require('../../ui/sass/app.scss');
import container from './container';

const foo = require('../../ui/twig/foo.twig');

function updateTitle() {
    const title = document.querySelector('.js-page-title');

    title.insertAdjacentHTML('beforebegin', foo({title: 'Hello World!'}));
    title.parentElement.removeChild(title);
};

document.addEventListener('DOMContentLoaded', () => {
    console.log(container.textEditor);
    container.textEditor.initialiseEditor(document.querySelector('.js-article-editor'));
    updateTitle();
});
