import TemplateRenderer from '../../../../src/ts/Library/TemplateRenderer/TemplateRenderer';
import { InsertPosition } from '../../../../src/ts/Library/TemplateRenderer/InsertPosition';

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
        TemplateRenderer.render({
            template: twig.template, 
            anchorElement: element, 
            data: templateData,
            position: InsertPosition.beforeend,
        });
        expect(twig.template).toHaveBeenCalledWith(templateData);
        expect(element.insertAdjacentHTML).toHaveBeenCalledWith('beforeend', '<div>world</div>');
    });
})
