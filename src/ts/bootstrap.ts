import { bottle } from './container';
import { loadComponents } from 'components/componentLoader';

document.addEventListener('DOMContentLoaded', () => {
    require('Sass/app.scss');

    const app = bottle.container.bootstrap;
    loadComponents();
    if (null === app) {
        return;
    }
    app.start();
});
