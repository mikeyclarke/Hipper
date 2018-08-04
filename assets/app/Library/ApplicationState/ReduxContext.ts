import * as redux from 'redux';
import reducerIndex from '../../redux/reducers/index';

export default class ReduxContext implements IContext {
    private store;

    constructor(initialState: object) {
        this.store = redux.createStore(reducerIndex);
    }

    getFragment(stateFragmentId : string) {
        return {'hello': 'world'};
    }

    get() {
        return this.store.getState();
    }

    subscribe(onStateChange : Function) {
        return () => {console.log('hello world')};
    }

    dispatch(action: any, payload: any) {
        this.store.dispatch(action(payload));
    }
}
