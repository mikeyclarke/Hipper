import { EventsHash } from './EventsHash';

export interface EventsEnabled {
    getEvents(): EventsHash;
}
