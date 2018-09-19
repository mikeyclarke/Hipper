import { bottle } from './container';

document.addEventListener('DOMContentLoaded', () => {
    const app = bottle.container.bootstrap;
    app.start();
});
