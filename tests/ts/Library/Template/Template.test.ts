import Template from '../../../../src/ts/Library/Template/Template';

describe('Template', () => {
    it('returns the result from a supplied template function', () => {
        const mockTemplateFunction = (data) => `hello ${data.test}`;
        const template = new Template(mockTemplateFunction);
        const result = template.render({test: 'test'});
        expect(result === 'hello test');
    });
});
