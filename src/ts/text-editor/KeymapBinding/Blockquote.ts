import { wrapIn } from 'prosemirror-commands';
import { MarkType, NodeType } from 'prosemirror-model';
import { Blockquote as BlockquoteNode } from 'text-editor/Node/Blockquote';
import { KeymapBindingInterface } from 'text-editor/KeymapBinding/KeymapBindingInterface';

export class Blockquote implements KeymapBindingInterface {
    get requirementType(): string | null {
        return 'node';
    }

    get requirement(): Function | null {
        return BlockquoteNode;
    }

    public getBindings(isMacOs: boolean, type?: MarkType | NodeType): object[] {
        if (!(type instanceof NodeType)) {
            return [];
        }

        return [
            { 'Mod->': wrapIn(type) },
        ];
    }
}
