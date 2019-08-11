import { chainCommands, exitCode } from 'prosemirror-commands';
import { MarkType, NodeType } from 'prosemirror-model';
import { HardBreak as HardBreakNode } from 'text-editor/Node/HardBreak';
import { KeymapBindingInterface } from 'text-editor/KeymapBinding/KeymapBindingInterface';

export class HardBreak implements KeymapBindingInterface {
    get requirementType(): string | null {
        return 'node';
    }

    get requirement(): Function | null {
        return HardBreakNode;
    }

    public getBindings(isMacOs: boolean, type?: MarkType | NodeType): object[] {
        if (!(type instanceof NodeType)) {
            return [];
        }

        const command = chainCommands(exitCode, (state, dispatch) => {
            if (dispatch) {
                dispatch(state.tr.replaceSelectionWith(type.create()).scrollIntoView());
            }
            return true;
        });

        const result: object[] = [
            { 'Mod-Enter': command },
            { 'Shift-Enter': command },
        ];

        if (isMacOs) {
            result.push({ 'Ctrl-Enter': command });
        }

        return result;
    }
}
