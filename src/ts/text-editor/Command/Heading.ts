import { EditorView } from 'prosemirror-view';
import { MarkType, NodeType } from 'prosemirror-model';
import { setBlockType } from 'prosemirror-commands';
import { CommandInterface, CommandRequirementType, GetCommandResult } from 'text-editor/Command/CommandInterface';
import HeadingNode from 'text-editor/Node/Heading';

export default class Heading implements CommandInterface {
    get name(): string {
        return 'heading';
    }

    get label(): string {
        return 'Make text into a heading';
    }

    get requirementType(): CommandRequirementType {
        return 'node';
    }

    get requirement(): Function | null {
        return HeadingNode;
    }

    public getCommand(type?: MarkType | NodeType, options?: Record<any, any>): GetCommandResult {
        if (!(type instanceof NodeType)) {
            throw new Error('Parameter type must be a NodeType');
        }

        let level = 1;
        if (options && options.level) {
            level = options.level;
        }

        const command = setBlockType(type, { level: level });

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
