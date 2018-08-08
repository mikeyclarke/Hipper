import HTMLInserter from '../../../../src/ts/Library/HTMLInserter/HTMLInserter';
import { InsertPoint } from '../../../../src/ts/Library/HTMLInserter/InsertPoint';

describe('HTMLInserter', () => {
    let relativeElement = document.createElement('div');

    it('invokes the dom function with correct params', () => {
        spyOn(relativeElement, 'insertAdjacentHTML');
        HTMLInserter.insert(relativeElement, InsertPoint.afterbegin, '<div>test</div>');
        expect(relativeElement.insertAdjacentHTML).toHaveBeenCalledTimes(1);
        expect(relativeElement.insertAdjacentHTML).toHaveBeenCalledWith('afterbegin', '<div>test</div>');
    });
})
