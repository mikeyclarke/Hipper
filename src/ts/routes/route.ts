export default interface RouteDefinition {
    path: string;
    controller: Function;
    requirements?: object;
    defaults?: object;
}
