import DOMQueryable from 'hleo/QuerySelector/DOMQueryable';

export default function querySelectorNotNull(context: DOMQueryable, selector: string): HTMLElement {
    const result = context.querySelector(selector);
    if (result) {
        return result;
    }
    throw new Error('querySelector yeilded no result');
}
