import HTMLInserter from '../../../../src/ts/Library/HTMLInserter/HTMLInserter';
import { InsertPoint } from '../../../../src/ts/Library/HTMLInserter/InsertPoint';

describe('HTMLInserter', () => {
    let element;
    let html;

    beforeEach(() => {
        element = {
            insertAdjacentHTML:(html) => true,
        };
        html = '<div>world</div>';
    });

    it('Passes template data to the twig and inserts html', () => {
        spyOn(element, 'insertAdjacentHTML');
        HTMLInserter.insertHTMLString(
            element,
            InsertPoint.beforeend,
            html
        );
        expect(element.insertAdjacentHTML).toHaveBeenCalledWith('beforeend', html);
    });
})
