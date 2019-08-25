import * as Bottle from 'bottlejs';

export class ContainerBuilder {
    public build(bottle: Bottle, serviceCollections: Function[], configs: object[], routes: Function): void {
        serviceCollections.forEach((collection) => {
            collection(bottle);
        });

        const config = Object.assign({}, ...configs, { routes: routes(bottle) });
        bottle.constant('config', config);
    }
}
