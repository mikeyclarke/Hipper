require('../app/app.scss');
import container from '../app/container';
import { ExampleAction } from './redux/actions';

document.addEventListener('DOMContentLoaded', () => {
    container.reduxContext.dispatch(ExampleAction, {test: 'we did it!'});
    console.log(container.stateFragmentBus.get());
});
