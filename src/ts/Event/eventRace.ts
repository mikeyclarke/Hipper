import timeout from 'Timeout/timeout';

type EventDef = [EventTarget, string, EventListener];

export default async function eventRace(
    timeLimitMilliseconds: number,
    onTimeUp: Function,
    ...eventDefinitions: EventDef[]
) {
    let winner: EventDef | null = null;
    const eventHandlerMap = new Map();

    eventDefinitions.forEach((eventDefinition) => {
        const [ eventTarget, name, listener ] = eventDefinition;
        const handler = (event: Event) => {
            eventTarget.removeEventListener(name, handler);
            if (null !== winner) {
                return;
            }
            winner = eventDefinition;
            listener(event);
        };
        eventTarget.addEventListener(name, handler);
        eventHandlerMap.set(eventDefinition, handler);
    });

    await timeout(timeLimitMilliseconds);

    eventDefinitions.forEach((eventDefinition) => {
        if (winner === eventDefinition) {
            return;
        }
        const [ eventTarget, name ] = eventDefinition;
        eventTarget.removeEventListener(name, eventHandlerMap.get(eventDefinition));
    });

    if (null === winner) {
        onTimeUp();
    }
}
