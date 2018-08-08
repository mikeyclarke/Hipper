export default class TemplateRenderer
{
    public static render(template: any, parent: Element, data = {}): void
    {
        parent.insertAdjacentHTML('beforeend', template(data));
    }
}
