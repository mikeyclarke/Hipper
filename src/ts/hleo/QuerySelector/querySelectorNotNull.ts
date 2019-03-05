import { DOMQueryable } from './DOMQueryable';

export function querySelectorNotNull(context: DOMQueryable, selector: string): HTMLElement {
    const result = context.querySelector(selector);
    if (result) {
        return result;
    }
    throw new Error('querySelector yeilded no result');
}