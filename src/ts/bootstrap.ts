require('Sass/app.scss');
import container from './container';

document.addEventListener('DOMContentLoaded', () => {
    const appBootstrap = new ApplicationBootstrap();
    appBootstrap.start();
});

class ApplicationBootstrap 
{
    public start(): void {
        this.initialiseNavigation();
        this.initialiseTextEditor();
    }

    private initialiseNavigation(): void
    {
        var nav = new container.navigationControl('.js-navigation-container');
    }

    private initialiseTextEditor(): void
    {
        container.textEditor.initialiseEditor(document.querySelector('.js-article-editor'));
    }
}
