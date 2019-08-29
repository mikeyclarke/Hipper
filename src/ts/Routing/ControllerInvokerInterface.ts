export default interface ControllerInvokerInterface {
    invoke(controller: any, routeParameters: Map<string, string>): void;
}
