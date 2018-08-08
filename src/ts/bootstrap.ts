require('../../ui/sass/app.scss');
import container from './container';
import ArticleEditor from './Library/ArticleEditor/ArticleEditor';

const foo = require('../../ui/twig/foo.twig');

function updateTitle() {
    const title = document.querySelector('.js-page-title');

    title.insertAdjacentHTML('beforebegin', foo({title: 'Hello World!'}));
    title.parentElement.removeChild(title);
};

document.addEventListener('DOMContentLoaded', () => {
    const editor = new ArticleEditor(document.querySelector('.js-article-editor'));
    updateTitle();
});
