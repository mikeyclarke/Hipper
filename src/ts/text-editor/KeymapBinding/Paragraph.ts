import { setBlockType } from 'prosemirror-commands';
import { MarkType, NodeType } from 'prosemirror-model';
import { Paragraph as ParagraphNode } from 'text-editor/Node/Paragraph';
import { KeymapBindingInterface } from 'text-editor/KeymapBinding/KeymapBindingInterface';

export class Paragraph implements KeymapBindingInterface {
    get requirementType(): string | null {
        return 'node';
    }

    get requirement(): Function | null {
        return ParagraphNode;
    }

    public getBindings(isMacOs: boolean, type?: MarkType | NodeType): object[] {
        if (!(type instanceof NodeType)) {
            return [];
        }

        return [
            { 'Mod-Alt-0': setBlockType(type) },
        ];
    }
}
