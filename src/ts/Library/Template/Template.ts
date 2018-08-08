export default class Template
{
    private templateFunction: Function;
    constructor(templateFunction: Function)
    {
        this.templateFunction = templateFunction;
    }

    public render(data: object): string
    {
        return this.templateFunction(data);
    }
}
