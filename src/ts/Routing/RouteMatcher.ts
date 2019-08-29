import { Key } from 'path-to-regexp';
import Route from 'Routing/Route';
import RouteCollection from 'Routing/RouteCollection';
import RouteCollectionParser from 'Routing/RouteCollectionParser';

type RouteMatch = [
    Route,
    Map<any, any> | null
];

export default class RouteMatcher {
    private readonly routeCollection: RouteCollection;
    private readonly staticRoutes: Record<string, string>;
    private readonly dynamicRoutes: Map<RegExp, any[]>;

    constructor(
        routeCollection: RouteCollection
    ) {
        this.routeCollection = routeCollection;

        const parser = new RouteCollectionParser();
        [this.staticRoutes, this.dynamicRoutes] = parser.parse(routeCollection);
    }

    public match(pathname: string): RouteMatch | null {
        if (this.staticRoutes[pathname]) {
            const routeName = this.staticRoutes[pathname];
            if (!this.routeCollection.has(routeName)) {
                throw new Error(`No route found with '${routeName}'`);
            }

            return [<Route> this.routeCollection.get(routeName), null];
        }

        for (const [pattern, definition] of this.dynamicRoutes) {
            const [routeName, placeholders] = definition;
            const match = pattern.exec(pathname);
            if (null !== match) {
                if (!this.routeCollection.has(routeName)) {
                    throw new Error(`No route found with '${routeName}'`);
                }

                match.shift();
                const matchedParameters = new Map();
                placeholders.forEach((placeholder: Key, index: number) => {
                    matchedParameters.set(placeholder.name, match[index]);
                });

                return [<Route> this.routeCollection.get(routeName), matchedParameters];
            }
        }

        return null;
    }
}
