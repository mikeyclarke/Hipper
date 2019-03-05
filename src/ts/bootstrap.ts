import { bottle } from './container';
import { loadComponents } from 'components/componentLoader';

document.addEventListener('DOMContentLoaded', () => {
    const app = bottle.container.bootstrap;
    loadComponents();
    app.start();
});
