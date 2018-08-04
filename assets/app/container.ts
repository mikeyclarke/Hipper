import * as Bottle from 'bottlejs';

import StateFragmentBus from './Library/ApplicationState/StateFragmentBus';
import ReduxContext from './Library/ApplicationState/ReduxContext';

const bottle = new Bottle();

bottle.factory('reduxContext', () => {
    return new ReduxContext({});
});

bottle.factory('stateFragmentBus', (container) => {
    return new StateFragmentBus(container.reduxContext, 'sample');
});

export default bottle.container;
