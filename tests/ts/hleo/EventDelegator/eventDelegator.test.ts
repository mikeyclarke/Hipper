import { EventDelegator } from 'hleo/EventDelegator/EventDelegator';

let eventFired: boolean = false;
let uiContainerElement: HTMLElement | null;
let eventTarget: HTMLElement | null;
let delegator: EventDelegator | null;

const eventsConsumer = {
    getEvents: () => {
        return {
            click: 'onClick',
        };
    },

    onClick: () => {
        eventFired = true;
    }
};

beforeEach(() => {
    uiContainerElement = document.createElement('div');
    eventTarget = document.createElement('div');
    eventTarget.classList.add('selector');
    uiContainerElement.appendChild(eventTarget);
    delegator = new EventDelegator(uiContainerElement);
    if (delegator) {
        delegator.setContext(eventsConsumer);
    }
});

afterEach(() => {
    uiContainerElement = null;
    delegator = null;
    eventFired = false;
    eventTarget = null;
});

test('Given an object with a valid event hash the event delegator successfully binds an event to a child element', () => {
    if (delegator && eventTarget) {
        delegator.delegate();
        eventTarget.click();
    }
    expect(eventFired).toBe(true);
});

test('After correctly binding an event the delegator successfully removes it', () => {
    if (delegator && eventTarget) {
        delegator.delegate();
        eventTarget.click();
    }
    expect(eventFired).toBe(true);
    eventFired = false;
    if (delegator && eventTarget) {
        delegator.undelegate();
        eventTarget.click();
    }
    expect(eventFired).toBe(false);
});
