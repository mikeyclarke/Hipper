import { Cookie } from 'Cookie/Cookie';
import { DocumentCookies } from 'Cookie/DocumentCookies';
import { TimeZoneRetriever } from 'TimeZone/TimeZoneRetriever';

const cookieName = 'tz';

export class TimeZoneCookie {
    private readonly documentCookies: DocumentCookies;
    private readonly timeZoneRetriever: TimeZoneRetriever;

    constructor(
        documentCookies: DocumentCookies,
        timeZoneRetriever: TimeZoneRetriever
    ) {
        this.documentCookies = documentCookies;
        this.timeZoneRetriever = timeZoneRetriever;
    }

    public createOrUpdate(): void {
        if (!this.documentCookies.enabled()) {
            return;
        }

        if (this.documentCookies.has(cookieName)) {
            this.updateCookie();
            return;
        }

        this.createCookie();
    }

    private createCookie(): void {
        const timeZoneName = this.timeZoneRetriever.getTimeZoneName();

        if (null === timeZoneName) {
            return;
        }

        this.storeCookie(timeZoneName);
    }

    private updateCookie(): void {
        const existingCookieValue = this.documentCookies.get(cookieName);
        const timeZoneName = this.timeZoneRetriever.getTimeZoneName();

        if (null === timeZoneName) {
            return;
        }

        if (existingCookieValue === timeZoneName) {
            return;
        }

        this.storeCookie(timeZoneName);
    }

    private storeCookie(timeZoneName: string): void {
        const cookie = new Cookie(
            cookieName,
            timeZoneName
        );
        this.documentCookies.add(cookie);
    }
}
