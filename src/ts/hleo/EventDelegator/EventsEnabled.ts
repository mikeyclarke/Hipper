import EventsHash from 'hleo/EventDelegator/EventsHash';

export default interface EventsEnabled {
    getEvents(): EventsHash;
}
