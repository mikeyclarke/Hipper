export function querySelectorNotNull(context: any, selector: string): HTMLElement {
    const result = context.querySelector(selector);
    if (result) {
        return result;
    }
    throw new Error('querySelector yeilded no result');
}