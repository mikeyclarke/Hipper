import * as Bottle from 'bottlejs';
import ContainerBuilder from 'ContainerBuilder';

export default abstract class Kernel {
    protected readonly bottle: Bottle;
    private readonly containerBuilder: ContainerBuilder;

    constructor() {
        this.bottle = new Bottle();
        this.containerBuilder = new ContainerBuilder();
    }

    public run(): void {
        this.configureContainer();

        this.onBeforeRouting();
        this.bottle.container.router.run();
    }

    private configureContainer(): void {
        const services = this.getServices();
        const configs = this.getConfigs();
        const routes = this.getRoutes();

        this.containerBuilder.build(this.bottle, services, configs, routes);
    }

    protected abstract onBeforeRouting(): void;

    protected abstract getServices(): Function[];

    protected abstract getConfigs(): object[];

    protected abstract getRoutes(): Function;
}
