require('../app/app.scss');
import StateFragmentBus from '../app/Library/ApplicationState/StateFragmentBus';
import ReduxContext from '../app/Library/ApplicationState/ReduxContext';
import container from '../app/container';

document.addEventListener('DOMContentLoaded', () => {
    const context = new ReduxContext({});
    const fragment = new StateFragmentBus(context, 'test');
    console.log(fragment.get());
    container.helloWorld.sayHello();
});
