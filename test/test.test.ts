import test from '../assets/app/Library/test';

describe("A suite", function() {
  it("contains spec with an expectation", function() {
    expect(test('name')).toBe('name');
  });
});
