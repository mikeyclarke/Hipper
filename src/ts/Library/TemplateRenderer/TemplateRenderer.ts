export default class TemplateRenderer
{
    public static render(options: any)
    {
        options.anchorElement.insertAdjacentHTML(options.position, options.template(options.data || {}));
    }
}
