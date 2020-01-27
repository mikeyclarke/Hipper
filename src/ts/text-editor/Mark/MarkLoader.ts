import MarkInterface from 'text-editor/Mark/MarkInterface';
import Code from 'text-editor/Mark/Code';
import Emphasis from 'text-editor/Mark/Emphasis';
import Link from 'text-editor/Mark/Link';
import Strike from 'text-editor/Mark/Strike';
import Strong from 'text-editor/Mark/Strong';

export default class MarkLoader {
    public getAllWithNames(names: string[]): MarkInterface[] {
        const available = [
            new Code(),
            new Emphasis(),
            new Link(),
            new Strike(),
            new Strong(),
        ];

        return available.filter(mark => names.includes(mark.name));
    }
}
