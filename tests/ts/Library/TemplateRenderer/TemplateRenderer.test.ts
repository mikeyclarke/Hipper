import TemplateRenderer from '../../../../src/ts/Library/TemplateRenderer/TemplateRenderer';

describe('TemplateRenderer', () => {
    let element;
    let data;
    let html;
    let stuff;

    beforeEach(() => {
        stuff = {
            template: function(data) {
                return html;
            },
        };
        data = {
            hello: 'world',
        };
        element = {
            insertAdjacentHTML:() => true,
        };
        stuff.html = `<div>${data.hello}</div>`;
    });

    it('does the thing', () => {
        spyOn(stuff, 'template');
        spyOn(element, 'insertAdjacentHTML');
        TemplateRenderer.render(stuff.template, element, data);
        expect(stuff.template).toHaveBeenCalledWith(data);
        expect(element.insertAdjacentHTML).toHaveBeenCalledWith('beforeend', html);
    });
})
