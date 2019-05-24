export class TimeZoneRetriever {
    public getTimeZoneName(): string | null {
        const dateTimeFormatter = new Intl.DateTimeFormat();
        const options = dateTimeFormatter.resolvedOptions();
        if (!Object.keys(options).includes('timeZone') || options.timeZone === undefined) {
            return null;
        }
        return options.timeZone;
    }
}
