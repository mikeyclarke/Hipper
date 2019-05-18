import { bottle } from './container';
import { loadComponents } from 'components/componentLoader';

document.addEventListener('DOMContentLoaded', () => {
    const app = bottle.container.bootstrap;
    loadComponents();
    if (null === app) {
        return;
    }
    app.start();
});
