export default class Route {
    private path!: string;
    private controller!: any;
    private requirements!: Map<string, string>;
    private defaults!: Map<string, any>;

    constructor(
        path: string,
        controller: any,
        requirements: object = {},
        defaults: object = {}
    ) {
        this.setPath(path);
        this.setController(controller);
        this.setRequirements(requirements);
        this.setDefaults(defaults);
    }

    public setPath(path: string): void {
        this.path = path;
    }

    public getPath(): string {
        return this.path;
    }

    public setController(controller: any): void {
        this.controller = controller;
    }

    public getController(): any {
        return this.controller;
    }

    public setRequirements(requirements: object): void {
        this.requirements = new Map(Object.entries(requirements));
    }

    public getRequirements(): Map<string, string> {
        return this.requirements;
    }

    public setDefaults(defaults: object): void {
        this.defaults = new Map(Object.entries(defaults));
    }

    public getDefaults(): Map<string, any> {
        return this.defaults;
    }
}
