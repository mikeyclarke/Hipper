import Bottle from 'bottlejs';
import HttpClient from 'Http/HttpClient';
import DocumentCookies from 'Cookie/DocumentCookies';
import FormSubmitHelper from 'Helper/FormSubmitHelper';
import TimeZoneCookie from 'TimeZone/TimeZoneCookie';
import TimeZoneRetriever from 'TimeZone/TimeZoneRetriever';
import CallbackControllerInvoker from 'Routing/CallbackControllerInvoker';
import RouteMatcher from 'Routing/RouteMatcher';
import Router from 'Routing/Router';

export default function sharedServices(bottle: Bottle): void {
    bottle.factory('httpClient', (container) => {
        return new HttpClient(
            container.config.csrf_token
        );
    });

    bottle.factory('documentCookies', () => new DocumentCookies());

    bottle.factory('formSubmitHelper', (container) => {
        return new FormSubmitHelper(
            container.httpClient
        );
    });

    bottle.factory('timeZoneRetriever', () => new TimeZoneRetriever());

    bottle.factory('timeZoneCookie', (container) => {
        return new TimeZoneCookie(
            container.documentCookies,
            container.timeZoneRetriever
        );
    });

    bottle.factory('routeMatcher', (container) => {
        return new RouteMatcher(
            container.config.routes
        );
    });

    bottle.service('callbackControllerInvoker', CallbackControllerInvoker);

    bottle.factory('router', (container) => {
        return new Router(
            container.routeMatcher,
            container.callbackControllerInvoker
        );
    });
}
