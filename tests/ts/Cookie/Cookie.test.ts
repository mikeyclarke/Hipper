import Cookie from 'Cookie/Cookie';

test('Cookie is correctly formatted', () => {
    const cookie = new Cookie('foo', 'bar');
    expect(cookie.toString()).toBe('foo=bar; path=/');
});

test('Cookie has an encoded value', () => {
    const value = 'Bar Baz & Mic drop';
    const cookie = new Cookie('foo', value);
    expect(cookie.toString()).toBe(`foo=${encodeURIComponent(value)}; path=/`);
});

test('Cookie has the provided expiry time', () => {
    const cookie = new Cookie('foo', 'bar', 'Wed, 22 May 2019 22:12:55 GMT');
    expect(cookie.toString()).toBe('foo=bar; path=/; expires=Wed, 22 May 2019 22:12:55 GMT');
});

test('Cookie has the provided path', () => {
    const cookie = new Cookie('foo', 'bar', null, null, '/path-name');
    expect(cookie.toString()).toBe('foo=bar; path=/path-name');
});

test('Cookie has the provided domain', () => {
    const cookie = new Cookie('foo', 'bar', null, 'usehipper.test');
    expect(cookie.toString()).toBe('foo=bar; path=/; domain=usehipper.test');
});
