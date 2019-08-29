import ControllerInvokerInterface from 'Routing/ControllerInvokerInterface';

export default class CallbackControllerInvoker implements ControllerInvokerInterface {
    public invoke(controller: any, routeParameters: Map<string, string>): void {
        if (!(controller instanceof Function)) {
            throw new Error('Controller is not a function');
        }

        const [controllerClass, method] = controller();
        controllerClass[method](routeParameters);
    }
}
