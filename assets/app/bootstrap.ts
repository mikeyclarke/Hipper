require('../app/app.scss');
import container from '../app/container';

document.addEventListener('DOMContentLoaded', () => {
    console.log(container.stateFragmentBus.get());
});
