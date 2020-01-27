import { undo, redo } from 'prosemirror-history';
import { undoInputRule } from 'prosemirror-inputrules';
import { MarkType, NodeType } from 'prosemirror-model';
import KeymapBindingInterface from 'text-editor/KeymapBinding/KeymapBindingInterface';

export default class History implements KeymapBindingInterface {
    get requirementType(): string | null {
        return null;
    }

    get requirement(): Function | null {
        return null;
    }

    public getBindings(isMacOs: boolean, type?: MarkType | NodeType): object[] {
        const result: object[] = [
            { 'Mod-z': undo },
            { 'Shift-Mod-z': redo },
            { Backspace: undoInputRule },
        ];

        if (isMacOs) {
            result.push({ 'Mod-y': redo });
        }

        return result;
    }
}
