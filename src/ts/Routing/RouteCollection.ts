import Route from 'Routing/Route';

export default class RouteCollection {
    private readonly routes: Map<string, Route>;

    constructor() {
        this.routes = new Map();
    }

    public add(name: string, route: Route): void {
        this.routes.set(name, route);
    }

    public has(name: string): boolean {
        return this.routes.has(name);
    }

    public get(name: string): Route | null {
        return this.routes.get(name) || null;
    }

    public all(): Map<string, Route> {
        return this.routes;
    }
}
