import { setBlockType } from 'prosemirror-commands';
import { MarkType, NodeType } from 'prosemirror-model';
import { Heading as HeadingNode } from 'text-editor/Node/Heading';
import { KeymapBindingInterface } from 'text-editor/KeymapBinding/KeymapBindingInterface';

export class Heading implements KeymapBindingInterface {
    get requirementType(): string | null {
        return 'node';
    }

    get requirement(): Function | null {
        return HeadingNode;
    }

    public getBindings(isMacOs: boolean, type?: MarkType | NodeType): object[] {
        if (!(type instanceof NodeType)) {
            return [];
        }

        const result: object[] = [];
        [1, 2, 3, 4, 5, 6].forEach(level => {
            const obj: Record<string, Function> = {};
            obj[`Mod-Alt-${level}`] = setBlockType(type, { level: level });
            result.push(obj);
        });

        return result;
    }
}
