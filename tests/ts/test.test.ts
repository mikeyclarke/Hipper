import test from '../../src/ts/Library/test';

describe("A suite", function() {
  it("contains spec with an expectation", function() {
    expect(test('name')).toBe('name');
  });
});
