import { toggleMark } from 'prosemirror-commands';
import { MarkType, NodeType } from 'prosemirror-model';
import { Strong as StrongMark } from 'text-editor/Mark/Strong';
import { KeymapBindingInterface } from 'text-editor/KeymapBinding/KeymapBindingInterface';

export class Strong implements KeymapBindingInterface {
    get requirementType(): string | null {
        return 'mark';
    }

    get requirement(): Function | null {
        return StrongMark;
    }

    public getBindings(isMacOs: boolean, type?: MarkType | NodeType): object[] {
        if (!(type instanceof MarkType)) {
            return [];
        }

        return [
            { 'Mod-b': toggleMark(type) },
            { 'Mod-B': toggleMark(type) },
        ];
    }
}
