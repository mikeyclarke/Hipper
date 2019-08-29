import * as Bottle from 'bottlejs';
import RouteDefinition from 'routes/route';
import RouteCollection from 'Routing/RouteCollection';
import Route from 'Routing/Route';

export default class ContainerBuilder {
    public build(bottle: Bottle, serviceCollections: Function[], configs: object[], routes: Function): void {
        serviceCollections.forEach((collection) => {
            collection(bottle);
        });

        const config = Object.assign({}, ...configs, { routes: this.routesToCollection(routes(bottle)) });
        bottle.constant('config', config);
    }

    private routesToCollection(routes: Record<string, RouteDefinition>): RouteCollection {
        const routeCollection = new RouteCollection();
        for (const [name, def] of Object.entries(routes)) {
            const route = new Route(
                def.path,
                def.controller,
                def.requirements || {},
                def.defaults || {}
            );
            routeCollection.add(name, route);
        }
        return routeCollection;
    }
}
