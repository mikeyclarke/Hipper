import container from './container';

document.addEventListener('DOMContentLoaded', () => {
    const app = container.bootstrap;
    app.bootstrap();
});
