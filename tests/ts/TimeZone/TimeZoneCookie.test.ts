import { Cookie } from 'Cookie/Cookie';
import { DocumentCookies } from 'Cookie/DocumentCookies';
import { TimeZoneCookie } from 'TimeZone/TimeZoneCookie';
import { TimeZoneRetriever } from 'TimeZone/TimeZoneRetriever';

jest.mock('Cookie/DocumentCookies');
jest.mock('TimeZone/TimeZoneRetriever');

let documentCookies: DocumentCookies;
let timeZoneRetriever: TimeZoneRetriever;
let timeZoneCookie: TimeZoneCookie;

beforeEach(() => {
    documentCookies = new DocumentCookies;
    timeZoneRetriever = new TimeZoneRetriever;

    timeZoneCookie = new TimeZoneCookie(
        documentCookies,
        timeZoneRetriever
    );
});

test('No cookie created if cookies are not enabled in the browser', () => {
    createDocumentCookiesEnabledExpectation(false);

    timeZoneCookie.createOrUpdate();

    expect(documentCookies.enabled).toHaveBeenCalled();
    expect(documentCookies.has).not.toHaveBeenCalled();
});

test('Cookie is created when doesn’t exist', () => {
    const timeZone = 'Europe/London';
    const cookie = new Cookie(
        'tz',
        timeZone
    );

    createDocumentCookiesEnabledExpectation(true);
    createDocumentCookiesHasExpectation(false);
    createTimeZoneRetrieverExpectation(timeZone);

    timeZoneCookie.createOrUpdate();

    expect(documentCookies.enabled).toHaveBeenCalled();
    expect(documentCookies.has).toHaveBeenCalled();
    expect(timeZoneRetriever.getTimeZoneName).toHaveBeenCalled();
    expect(documentCookies.add).toHaveBeenCalledWith(cookie);
});

test('Not created if time zone is not available', () => {
    createDocumentCookiesEnabledExpectation(true);
    createDocumentCookiesHasExpectation(false);
    createTimeZoneRetrieverExpectation(null);

    timeZoneCookie.createOrUpdate();

    expect(documentCookies.enabled).toHaveBeenCalled();
    expect(documentCookies.has).toHaveBeenCalled();
    expect(timeZoneRetriever.getTimeZoneName).toHaveBeenCalled();
    expect(documentCookies.add).not.toHaveBeenCalled();
});

test('Updated if it already exists and time zone has changed', () => {
    const previousTimeZone = 'Europe/London';
    const currentTimeZone = 'America/La_Paz';
    const cookie = new Cookie(
        'tz',
        currentTimeZone
    );

    createDocumentCookiesEnabledExpectation(true);
    createDocumentCookiesHasExpectation(true);
    createDocumentCookiesGetExpectation(previousTimeZone);
    createTimeZoneRetrieverExpectation(currentTimeZone);

    timeZoneCookie.createOrUpdate();

    expect(documentCookies.enabled).toHaveBeenCalled();
    expect(documentCookies.has).toHaveBeenCalled();
    expect(documentCookies.get).toHaveBeenCalled();
    expect(timeZoneRetriever.getTimeZoneName).toHaveBeenCalled();
    expect(documentCookies.add).toHaveBeenCalledWith(cookie);
});

test('Not updated if time zone is unchanged', () => {
    const previousTimeZone = 'Europe/London';
    const currentTimeZone = previousTimeZone;

    createDocumentCookiesEnabledExpectation(true);
    createDocumentCookiesHasExpectation(true);
    createDocumentCookiesGetExpectation(previousTimeZone);
    createTimeZoneRetrieverExpectation(currentTimeZone);

    timeZoneCookie.createOrUpdate();

    expect(documentCookies.enabled).toHaveBeenCalled();
    expect(documentCookies.has).toHaveBeenCalled();
    expect(documentCookies.get).toHaveBeenCalled();
    expect(timeZoneRetriever.getTimeZoneName).toHaveBeenCalled();
    expect(documentCookies.add).not.toHaveBeenCalled();
});

test('Not updated if time zone is unavailable', () => {
    const previousTimeZone = 'Europe/London';

    createDocumentCookiesEnabledExpectation(true);
    createDocumentCookiesHasExpectation(true);
    createDocumentCookiesGetExpectation(previousTimeZone);
    createTimeZoneRetrieverExpectation(null);

    timeZoneCookie.createOrUpdate();

    expect(documentCookies.enabled).toHaveBeenCalled();
    expect(documentCookies.has).toHaveBeenCalled();
    expect(documentCookies.get).toHaveBeenCalled();
    expect(timeZoneRetriever.getTimeZoneName).toHaveBeenCalled();
    expect(documentCookies.add).not.toHaveBeenCalled();
});

function createDocumentCookiesGetExpectation(result: string) {
    const getMethod = jest.spyOn(documentCookies, 'get');
    getMethod.mockImplementation(() => result);
}

function createTimeZoneRetrieverExpectation(result: string | null) {
    const getTimeZoneNameMethod = jest.spyOn(timeZoneRetriever, 'getTimeZoneName');
    getTimeZoneNameMethod.mockImplementation(() => result);
}

function createDocumentCookiesHasExpectation(result: boolean) {
    const hasMethod = jest.spyOn(documentCookies, 'has');
    hasMethod.mockImplementation(() => result);
}

function createDocumentCookiesEnabledExpectation(result: boolean) {
    const enabledMethod = jest.spyOn(documentCookies, 'enabled');
    enabledMethod.mockImplementation(() => result);
}
