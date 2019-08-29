import Route from 'Routing/Route';
import RouteMatcher from 'Routing/RouteMatcher';
import ControllerInvokerInterface from 'Routing/ControllerInvokerInterface';

export default class Router {
    private readonly routeMatcher: RouteMatcher;
    private readonly controllerInvoker: ControllerInvokerInterface;

    constructor(
        routeMatcher: RouteMatcher,
        controllerInvoker: ControllerInvokerInterface
    ) {
        this.routeMatcher = routeMatcher;
        this.controllerInvoker = controllerInvoker;
    }

    public run(): void {
        const pathname = window.location.pathname;
        const routeResult = this.routeMatcher.match(pathname);
        if (null !== routeResult) {
            this.callRouteController(...routeResult);
        }
    }

    private callRouteController(route: Route, matchedParameters: Map<any, any> | null = null): void {
        const controller = route.getController();
        let routeParameters = new Map([...route.getDefaults()]);

        if (null !== matchedParameters) {
            routeParameters = new Map([...routeParameters, ...matchedParameters]);
        }

        this.controllerInvoker.invoke(controller, routeParameters);
    }
}
