import * as Bottle from 'bottlejs';
import { HttpClient } from 'Http/HttpClient';
import { DocumentCookies } from 'Cookie/DocumentCookies';
import { TimeZoneCookie } from 'TimeZone/TimeZoneCookie';
import { TimeZoneRetriever } from 'TimeZone/TimeZoneRetriever';
import { RouteDefinition } from 'routes/route';

export const sharedServices = function (bottle: Bottle): void {
    bottle.factory('httpClient', (container) => {
        return new HttpClient(
            container.config.csrf_token
        );
    });

    bottle.factory('documentCookies', () => new DocumentCookies());

    bottle.factory('timeZoneRetriever', () => new TimeZoneRetriever());

    bottle.factory('timeZoneCookie', (container) => {
        return new TimeZoneCookie(
            container.documentCookies,
            container.timeZoneRetriever
        );
    });

    bottle.factory('bootstrap', (container) => {
        const path: string = window.location.pathname;
        const routes: Record<string, Function> = {};
        Object.entries(<Record<string, RouteDefinition>> container.config.routes).forEach(([name, route]) => {
            routes[route.path] = route.controller;
        });

        if (routes[path]) {
            return routes[path];
        }
        return null;
    });
};
