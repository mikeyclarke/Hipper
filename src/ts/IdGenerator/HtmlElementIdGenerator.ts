export default class HtmlElementIdGenerator {
    public generate(prefix: string | null = null, prefixJoinChar: string = '-'): string {
        const rand = (
            Number(String(Math.random()).slice(2)) +
            Date.now() +
            Math.round(performance.now())
        ).toString(36).substr(2, 9);

        if (null !== prefix) {
            return `${prefix}${prefixJoinChar}${rand}`;
        }

        return rand;
    }
}
