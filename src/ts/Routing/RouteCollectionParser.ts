import { pathToRegexp as pathToRegExp, Key } from 'path-to-regexp';
import RouteCollection from 'Routing/RouteCollection';

export default class RouteCollectionParser {
    public parse(routes: RouteCollection): any[] {
        const staticRoutes: Record<string, string> = {};
        const dynamicRoutes = new Map();

        for (const [name, route] of routes.all()) {
            if (route.getPath().indexOf(':') === -1) {
                staticRoutes[route.getPath()] = name;
                continue;
            }

            let path = route.getPath();
            for (const [prop, rule] of route.getRequirements()) {
                path = path.replace(`:${prop}`, `:${prop}(${rule})`);
            }

            const placeholders: Key[] = [];
            const regex = pathToRegExp(path, placeholders);
            dynamicRoutes.set(regex, [name, placeholders]);
        }

        return [staticRoutes, dynamicRoutes];
    }
}
