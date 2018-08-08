require('../../ui/sass/app.scss');
import container from './container';
import ArticleEditor from './Library/ArticleEditor/ArticleEditor';

document.addEventListener('DOMContentLoaded', () => {
    const editor = new ArticleEditor(document.querySelector('.js-article-editor'));
});
