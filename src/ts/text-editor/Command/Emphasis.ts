import { EditorView } from 'prosemirror-view';
import { Mark, MarkType, NodeType } from 'prosemirror-model';
import { toggleMark } from 'prosemirror-commands';
import { CommandInterface, CommandRequirementType, GetCommandResult } from 'text-editor/Command/CommandInterface';
import EmphasisMark from 'text-editor/Mark/Emphasis';

export default class Emphasis implements CommandInterface {
    get name(): string {
        return 'emphasis';
    }

    get label(): string {
        return 'Make text italic';
    }

    get requirementType(): CommandRequirementType {
        return 'mark';
    }

    get requirement(): Function | null {
        return EmphasisMark;
    }

    public getCommand(type?: MarkType | NodeType, options?: Record<any, any>): GetCommandResult {
        if (!(type instanceof MarkType)) {
            throw new Error('Parameter type must be a MarkType');
        }

        const command = toggleMark(type);

        return {
            execute(editorView: EditorView): void {
                command(editorView.state, editorView.dispatch);
            },

            isAvailable(editorView: EditorView): boolean {
                return command(editorView.state);
            },

            isApplied(editorView: EditorView): boolean {
                const { from, $from, to, empty } = editorView.state.selection;
                if (empty) {
                    return type.isInSet(editorView.state.storedMarks || $from.marks()) instanceof Mark;
                }
                return editorView.state.doc.rangeHasMark(from, to, type);
            },
        };
    }
}
