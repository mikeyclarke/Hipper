import { Cookie } from 'Cookie/Cookie';

export class DocumentCookies {
    public enabled(): boolean {
        return navigator.cookieEnabled;
    }

    public add(cookie: Cookie): void {
        document.cookie = cookie.toString();
    }

    public has(name: string): boolean {
        return null !== this.getCookieValueByName(name);
    }

    public get(name: string): string | null {
        const cookie = this.getCookieValueByName(name);
        return (null !== cookie) ? decodeURIComponent(cookie) : null;
    }

    private getCookieValueByName(name: string): string | null {
        const result = document.cookie.match(`(^|; )${name}=([^;]*)`);
        return (null !== result) ? result[2] : null;
    }
}
