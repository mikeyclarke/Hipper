import { toggleMark } from 'prosemirror-commands';
import { MarkType, NodeType } from 'prosemirror-model';
import EmphasisMark from 'text-editor/Mark/Emphasis';
import KeymapBindingInterface from 'text-editor/KeymapBinding/KeymapBindingInterface';

export default class Emphasis implements KeymapBindingInterface {
    get requirementType(): string | null {
        return 'mark';
    }

    get requirement(): Function | null {
        return EmphasisMark;
    }

    public getBindings(isMacOs: boolean, type?: MarkType | NodeType): object[] {
        if (!(type instanceof MarkType)) {
            return [];
        }

        return [
            { 'Mod-i': toggleMark(type) },
            { 'Mod-I': toggleMark(type) },
        ];
    }
}
