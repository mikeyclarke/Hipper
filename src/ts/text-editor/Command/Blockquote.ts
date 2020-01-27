import { EditorView } from 'prosemirror-view';
import { MarkType, NodeType } from 'prosemirror-model';
import { wrapIn } from 'prosemirror-commands';
import { CommandInterface, CommandRequirementType, GetCommandResult } from 'text-editor/Command/CommandInterface';
import BlockquoteNode from 'text-editor/Node/Blockquote';

export default class Blockquote implements CommandInterface {
    get name(): string {
        return 'blockquote';
    }

    get label(): string {
        return 'Make text a blockquote';
    }

    get requirementType(): CommandRequirementType {
        return 'node';
    }

    get requirement(): Function | null {
        return BlockquoteNode;
    }

    public getCommand(type?: MarkType | NodeType, options?: Record<any, any>): GetCommandResult {
        if (!(type instanceof NodeType)) {
            throw new Error('Parameter type must be a NodeType');
        }

        const command = wrapIn(type);

        return {
            execute(editorView: EditorView): void {
                command(editorView.state, editorView.dispatch);
            },

            isAvailable(editorView: EditorView): boolean {
                return command(editorView.state);
            },

            isApplied(editorView: EditorView): boolean {
                return false;
            },
        };
    }
}
