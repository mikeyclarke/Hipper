interface IContext {
    get(),
    getFragment(stateFragmentId : string),
    subscribe(onStateChange : Function),
}
