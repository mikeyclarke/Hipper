import { EditorView } from 'prosemirror-view';
import { MarkType, NodeType } from 'prosemirror-model';
import { wrapIn } from 'prosemirror-commands';
import { CommandInterface, CommandRequirementType, GetCommandResult } from 'text-editor/Command/CommandInterface';
import { CodeBlock as CodeBlockNode } from 'text-editor/Node/CodeBlock';

export class CodeBlock implements CommandInterface {
    get name(): string {
        return 'code_block';
    }

    get label(): string {
        return 'Make text a code block';
    }

    get requirementType(): CommandRequirementType {
        return 'node';
    }

    get requirement(): Function | null {
        return CodeBlockNode;
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
