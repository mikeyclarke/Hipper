import container from './container';

document.addEventListener('DOMContentLoaded', () => {
    const appBootstrap = new ApplicationBootstrap();
});

class ApplicationBootstrap 
{
    private routes: object = {
        '/': this.app,
        '/sign-up': this.signup,
    }

    constructor()
    {
        const path: string = window.location.pathname;
        if (this.routes[path]) {
            this.routes[path].bind(this)();
        } else {
            throw new Error('no path found for bootstrapping');
        }
    }

    private app(): void
    {
        require('Sass/app.scss');
        var nav = new container.navigationControl('.js-navigation-container');
        container.textEditor.initialiseEditor(document.querySelector('.js-article-editor'));
    }

    private signup(): void
    {
        require('Sass/signup.scss');
    }
}
