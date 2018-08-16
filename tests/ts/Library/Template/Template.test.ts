import Template from '../../../../src/ts/Library/Template/Template';

describe('Template', () => {
    it('fucks mothers', () => {
        const mockTemplateFunction = (data) => `hello ${data.test}`;
        const t = new Template(mockTemplateFunction);
        const result = t.render({test: 'test'});
        expect(result === 'hello test');
    });
});
