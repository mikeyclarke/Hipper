import TemplateRenderer from '../../../../src/ts/Library/TemplateRenderer/TemplateRenderer';

describe('TemplateRenderer', () => {
    let element;
    let templateData;
    let twig;

    beforeEach(() => {
        twig = {
            template: (data) => {
                return `<div>${data.hello}</div>`;
            },
        };
        templateData = {
            hello: 'world',
        };
        element = {
            insertAdjacentHTML:(html) => true,
        };
    });

    it('Passes template data to the twig and inserts html', () => {
        spyOn(twig, 'template').and.callThrough();;
        spyOn(element, 'insertAdjacentHTML');
        TemplateRenderer.render(twig.template, element, templateData);
        expect(twig.template).toHaveBeenCalledWith(templateData);
        expect(element.insertAdjacentHTML).toHaveBeenCalledWith('beforeend', '<div>world</div>');
    });
})
