import { EditorView } from 'prosemirror-view';
import { MarkType, NodeType } from 'prosemirror-model';
import { wrapInList } from 'prosemirror-schema-list';
import { CommandInterface, CommandRequirementType, GetCommandResult } from 'text-editor/Command/CommandInterface';
import { UnorderedList as UnorderedListNode } from 'text-editor/Node/UnorderedList';

export class UnorderedList implements CommandInterface {
    get name(): string {
        return 'unordered_list';
    }

    get label(): string {
        return 'Make text into a bulleted list';
    }

    get requirementType(): CommandRequirementType {
        return 'node';
    }

    get requirement(): Function | null {
        return UnorderedListNode;
    }

    public getCommand(type?: MarkType | NodeType, options?: Record<any, any>): GetCommandResult {
        if (!(type instanceof NodeType)) {
            throw new Error('Parameter type must be a NodeType');
        }

        const command = wrapInList(type);

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
