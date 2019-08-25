import * as Bottle from 'bottlejs';
import ContainerBuilder from 'ContainerBuilder';
import loadComponents from 'components/componentLoader';

export default abstract class Kernel {
    private readonly bottle: Bottle;
    private readonly containerBuilder: ContainerBuilder;

    constructor() {
        this.bottle = new Bottle();
        this.containerBuilder = new ContainerBuilder();
    }

    public run(): void {
        this.configureContainer();

        loadComponents();
        this.bottle.container.timeZoneCookie.createOrUpdate();
        if (null !== this.bottle.container.bootstrap) {
            const controller = this.bottle.container.bootstrap();
            controller.start();
        }
    }

    private configureContainer(): void {
        const services = this.getServices();
        const configs = this.getConfigs();
        const routes = this.getRoutes();

        this.containerBuilder.build(this.bottle, services, configs, routes);
    }

    protected abstract getServices(): Function[];

    protected abstract getConfigs(): object[];

    protected abstract getRoutes(): Function;
}
