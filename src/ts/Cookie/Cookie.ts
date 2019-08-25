export default class Cookie {
    private readonly name: string;
    private readonly value: string;
    private readonly expires: string | null;
    private readonly domain: string | null;
    private readonly path: string;

    constructor(
        name: string,
        value: string = '',
        expires: string | null = null,
        domain: string | null = null,
        path: string = '/'
    ) {
        this.name = name;
        this.value = value;
        this.expires = expires;
        this.domain = domain;
        this.path = path;
    }

    public toString(): string {
        let str = `${this.name}=${encodeURIComponent(this.value)}; path=${this.path}`;

        if (null !== this.expires) {
            str += `; expires=${this.expires}`;
        }

        if (null !== this.domain) {
            str += `; domain=${this.domain}`;
        }

        return str;
    }
}
