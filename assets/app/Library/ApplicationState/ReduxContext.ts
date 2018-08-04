export default class ReduxContext implements IContext {
    private store;

    constructor(initialState: object) {
        this.store = initialState;
    }

    getFragment(stateFragmentId : string) {
        return {'hello': 'world'};
    }

    get() {
        return {'hello': 'world'};
    }

    subscribe(onStateChange : Function) {
        return () => {console.log('hello world')};
    }
}
