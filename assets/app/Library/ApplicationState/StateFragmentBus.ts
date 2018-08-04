/// <reference path="./IContext.ts" />

export default class StateFragmentBus {
    private context : IContext;
    private stateFragmentId : string;
    private currentState : object;
    private onChange : Function;
    private unsubscribeMethod : Function;

    constructor(context: IContext, stateFragmentId: string) {
        this.context = context;
        this.stateFragmentId = stateFragmentId;
    }

    public get(): object {
        return this.context.get();
    }

    public subscribe(onChange): void {
        this.onChange = onChange;
        this.unsubscribeMethod = this.context.subscribe(this.onStateChange.bind(this));
    }

    public unsubscribe(): void {
        this.unsubscribeMethod();
    }

    private onStateChange(): void {
        const nextState = this.get();
        if (nextState !== this.currentState) {
            this.currentState = nextState;
            this.onChange(this.currentState);
        }
    }
}
